<?php

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) die( '-1' );

/**
 * Exports the WordPress database to a plain .sql file.
 *
 * Never uses esc_sql() / wpdb::_real_escape(): since WP 4.8.3 that call replaces every
 * literal '%' with an HMAC placeholder which is only ever resolved by the `query`
 * filter right before wpdb executes a real query. A dump file is never executed as a
 * wpdb query, so the placeholder would stay in the file and corrupt length-prefixed
 * serialized values on restore. Instead, values are written type-correctly (NULL,
 * unquoted numerics, hex-encoded binary) and strings are escaped directly via
 * mysqli_real_escape_string(), which does not touch '%'.
 */
class DB_Exporter {

	/** @var wpdb */
	private $wpdb;

	/** Max bytes of VALUES data per multi-row INSERT statement. */
	private const MAX_INSERT_BYTES = 1_000_000;

	/** Rows fetched per SELECT batch. */
	private const BATCH_SIZE = 1000;

	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	/**
	 * Export the full database to $file_path.
	 *
	 * @throws Exception On write failure or failed self-check.
	 */
	public function export( string $file_path ): void {
		$handle = fopen( $file_path, 'w' );
		if ( ! $handle ) {
			throw new Exception( 'Could not open export file for writing.' );
		}

		try {
			$this->write( $handle, "SET NAMES utf8mb4;\n" );
			$this->write( $handle, "SET FOREIGN_KEY_CHECKS = 0;\n" );
			$this->write( $handle, "SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';\n\n" );

			// Consistent snapshot so tables read later in the loop see the same point in
			// time as tables read first (only effective for InnoDB; MyISAM has no MVCC).
			$this->wpdb->query( 'SET TRANSACTION ISOLATION LEVEL REPEATABLE READ' );
			$this->wpdb->query( 'START TRANSACTION WITH CONSISTENT SNAPSHOT' );

			try {
				foreach ( $this->get_base_tables() as $table ) {
					$dumped = $this->export_table( $handle, $table );
					$actual = (int) $this->wpdb->get_var( "SELECT COUNT(*) FROM `$table`" );

					if ( $dumped !== $actual ) {
						throw new Exception( sprintf(
							'Row count mismatch for table `%s`: dumped %d rows, snapshot has %d.',
							$table, $dumped, $actual
						) );
					}
				}
			} finally {
				// Read-only transaction: always safe to commit, success or failure.
				$this->wpdb->query( 'COMMIT' );
			}

			$this->write( $handle, "SET FOREIGN_KEY_CHECKS = 1;\n" );
		} finally {
			fclose( $handle );
		}

		$this->verify_file( $file_path );
	}

	/**
	 * @return string[] Base table names, excluding views.
	 */
	private function get_base_tables(): array {
		return $this->wpdb->get_col( "SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'" );
	}

	/**
	 * @return int Number of rows written for $table.
	 */
	private function export_table( $handle, string $table ): int {
		$this->write( $handle, "DROP TABLE IF EXISTS `$table`;\n" );
		$create_table = $this->wpdb->get_var( "SHOW CREATE TABLE `$table`", 1 );
		$this->write( $handle, $create_table . ";\n\n" );

		$column_types = $this->get_column_types( $table );
		$columns      = array_keys( $column_types );
		$order_by     = $this->get_order_by_clause( $table, $columns );

		$row_count   = 0;
		$offset      = 0;
		$buffer      = '';
		$buffer_rows = 0;

		while ( true ) {
			$rows = $this->wpdb->get_results(
				$this->wpdb->prepare(
					"SELECT * FROM `$table` ORDER BY $order_by LIMIT %d OFFSET %d",
					self::BATCH_SIZE,
					$offset
				),
				ARRAY_A
			);

			if ( empty( $rows ) ) break;

			foreach ( $rows as $row ) {
				$tuple = '(' . implode( ', ', $this->format_row( $row, $column_types ) ) . ')';

				if ( $buffer_rows > 0 && strlen( $buffer ) + strlen( $tuple ) > self::MAX_INSERT_BYTES ) {
					$this->write_insert( $handle, $table, $columns, $buffer );
					$buffer      = '';
					$buffer_rows = 0;
				}

				$buffer      .= ( $buffer_rows > 0 ? ",\n" : '' ) . $tuple;
				$buffer_rows++;
				$row_count++;
			}

			$offset += self::BATCH_SIZE;
		}

		if ( $buffer_rows > 0 ) {
			$this->write_insert( $handle, $table, $columns, $buffer );
		}

		$this->write( $handle, "\n" );

		return $row_count;
	}

	private function write_insert( $handle, string $table, array $columns, string $values_sql ): void {
		$this->write( $handle, "INSERT INTO `$table` (`" . implode( '`, `', $columns ) . "`) VALUES\n$values_sql;\n" );
	}

	/**
	 * @param array $row          Column => value, as returned by wpdb (ARRAY_A).
	 * @param array $column_types Column => MySQL type, as returned by SHOW COLUMNS.
	 * @return string[] SQL literals in row order.
	 */
	private function format_row( array $row, array $column_types ): array {
		$values = [];
		foreach ( $row as $column => $value ) {
			$values[] = $this->format_value( $value, $column_types[ $column ] );
		}
		return $values;
	}

	private function format_value( ?string $value, string $column_type ): string {
		if ( $value === null ) return 'NULL';

		if ( $this->is_binary_type( $column_type ) ) {
			return $value === '' ? "''" : '_binary 0x' . bin2hex( $value );
		}

		if ( $this->is_numeric_type( $column_type ) ) {
			return $value;
		}

		return "'" . mysqli_real_escape_string( $this->wpdb->dbh, $value ) . "'";
	}

	private function is_numeric_type( string $type ): bool {
		return (bool) preg_match( '/^(tinyint|smallint|mediumint|int|bigint|decimal|numeric|float|double)\b/i', $type );
	}

	private function is_binary_type( string $type ): bool {
		return (bool) preg_match( '/^(binary|varbinary|tinyblob|blob|mediumblob|longblob|bit)\b/i', $type );
	}

	/**
	 * @return array Column name => MySQL type (e.g. "varchar(255)"), in table order.
	 */
	private function get_column_types( string $table ): array {
		$rows  = $this->wpdb->get_results( "SHOW COLUMNS FROM `$table`", ARRAY_A );
		$types = [];
		foreach ( $rows as $row ) {
			$types[ $row['Field'] ] = $row['Type'];
		}
		return $types;
	}

	/**
	 * Order by the primary key when there is one, so LIMIT/OFFSET pagination is
	 * deterministic. Tables without a primary key fall back to ordering by every
	 * column - correct, but can be slow on large tables with no suitable index.
	 */
	private function get_order_by_clause( string $table, array $all_columns ): string {
		$columns = $this->get_primary_key_columns( $table ) ?: $all_columns;
		return '`' . implode( '`, `', $columns ) . '`';
	}

	private function get_primary_key_columns( string $table ): array {
		$rows = $this->wpdb->get_results( "SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'", ARRAY_A );
		if ( empty( $rows ) ) return [];

		usort( $rows, fn( $a, $b ) => (int) $a['Seq_in_index'] <=> (int) $b['Seq_in_index'] );
		return array_column( $rows, 'Column_name' );
	}

	private function write( $handle, string $data ): void {
		$written = fwrite( $handle, $data );
		if ( $written === false || $written < strlen( $data ) ) {
			throw new Exception( 'Failed to write to export file (disk full?).' );
		}
	}

	/**
	 * Fail loudly on a provably broken dump.
	 *
	 * @throws Exception
	 */
	private function verify_file( string $file_path ): void {
		$handle = fopen( $file_path, 'r' );
		if ( ! $handle ) {
			throw new Exception( 'Could not reopen export file for verification.' );
		}

		$last_line = '';

		try {
			while ( ( $line = fgets( $handle ) ) !== false ) {
				if ( preg_match( '/\{[0-9a-f]{64}\}/', $line ) ) {
					throw new Exception( 'Export contains an unresolved wpdb placeholder escape - dump is corrupt.' );
				}
				if ( trim( $line ) !== '' ) {
					$last_line = $line;
				}
			}
		} finally {
			fclose( $handle );
		}

		if ( ! preg_match( '/;\s*$/', $last_line ) ) {
			throw new Exception( 'Export file does not end with a complete statement - dump is truncated.' );
		}
	}
}