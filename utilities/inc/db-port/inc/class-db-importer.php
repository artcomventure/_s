<?php

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) die( '-1' );

/**
 * Imports a plain .sql dump produced by DB_Exporter (or any standard mysqldump-style
 * file) back into the WordPress database.
 *
 * Executes one statement at a time instead of sending the whole file as a single
 * multi_query() call: a large dump sent in one packet can exceed the server's
 * max_allowed_packet and trigger "MySQL server has gone away".
 */
class DB_Importer {

	/** @var wpdb */
	private $wpdb;

	/**
	 * A placeholder candidate must repeat at least this often to be treated as the
	 * known wpdb escape bug rather than coincidental data (see repair_placeholder_escapes()).
	 */
	private const PLACEHOLDER_MIN_OCCURRENCES = 5;

	/** Number of placeholder occurrences repaired during the last import(), if any. */
	private int $repaired_placeholder_count = 0;

	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	/**
	 * @throws Exception On the first failing statement, or a truncated dump.
	 */
	public function import( string $file_path ): void {
		$sql = file_get_contents( $file_path );
		if ( $sql === false ) {
			throw new Exception( 'Could not read dump file.' );
		}

		[ $sql, $this->repaired_placeholder_count ] = $this->repair_placeholder_escapes( $sql );

		foreach ( $this->split_statements( $sql ) as $statement ) {
			if ( $this->wpdb->query( $statement ) === false ) {
				$preview = strlen( $statement ) > 200 ? substr( $statement, 0, 200 ) . '...' : $statement;
				throw new Exception( sprintf( '%s (statement: %s)', $this->wpdb->last_error, $preview ) );
			}
		}
	}

	/**
	 * Number of wpdb placeholder-escape occurrences repaired during the last import().
	 */
	public function get_repaired_placeholder_count(): int {
		return $this->repaired_placeholder_count;
	}

	/**
	 * Detect and undo wpdb's placeholder-escape bug (see DB_Exporter docblock) in dumps
	 * produced by the old, unfixed export script. wpdb::remove_placeholder_escape()
	 * cannot be reused here: its placeholder is a fresh per-request random value, not
	 * the one baked into a dump from a previous request. Instead, the placeholder is
	 * recovered directly from the file: a single export request reuses the exact same
	 * hash for every escaped '%', so it repeats identically throughout the dump.
	 *
	 * @return array{0: string, 1: int} [possibly repaired SQL, number of occurrences fixed]
	 * @throws Exception If multiple distinct placeholder candidates are found - too
	 *                    ambiguous to repair automatically.
	 */
	private function repair_placeholder_escapes( string $sql ): array {
		if ( ! preg_match_all( '/\{[0-9a-f]{64}\}/', $sql, $matches ) ) {
			return [ $sql, 0 ];
		}

		$counts     = array_count_values( $matches[0] );
		$candidates = array_filter( $counts, fn( $count ) => $count >= self::PLACEHOLDER_MIN_OCCURRENCES );

		if ( empty( $candidates ) ) {
			// Too rare to plausibly be the escape bug - leave untouched rather than guess.
			return [ $sql, 0 ];
		}

		if ( count( $candidates ) > 1 ) {
			throw new Exception( 'Dump contains multiple distinct wpdb placeholder-escape patterns - cannot repair automatically, inspect the file manually.' );
		}

		$placeholder = array_key_first( $candidates );

		return [ str_replace( $placeholder, '%', $sql ), $candidates[ $placeholder ] ];
	}

	/**
	 * Split a SQL dump into individual statements. Tracks quoted strings, backtick
	 * identifiers and comments so a semicolon inside any of those doesn't end the
	 * statement early.
	 *
	 * @return Generator<string>
	 * @throws Exception If the file ends mid-statement (truncated dump).
	 */
	private function split_statements( string $sql ): Generator {
		$state = 'none'; // none | single | double | backtick | line_comment | block_comment
		$start = 0;
		$len   = strlen( $sql );

		for ( $i = 0; $i < $len; $i++ ) {
			$char = $sql[ $i ];
			$next = $i + 1 < $len ? $sql[ $i + 1 ] : '';

			switch ( $state ) {
				case 'single':
				case 'double':
					$quote = $state === 'single' ? "'" : '"';
					if ( $char === '\\' ) $i++;
					elseif ( $char === $quote ) {
						if ( $next === $quote ) $i++;
						else $state = 'none';
					}
					break;

				case 'backtick':
					if ( $char === '`' ) {
						if ( $next === '`' ) $i++;
						else $state = 'none';
					}
					break;

				case 'line_comment':
					if ( $char === "\n" ) $state = 'none';
					break;

				case 'block_comment':
					if ( $char === '*' && $next === '/' ) {
						$state = 'none';
						$i++;
					}
					break;

				default: // 'none'
					if ( $char === "'" ) $state = 'single';
					elseif ( $char === '"' ) $state = 'double';
					elseif ( $char === '`' ) $state = 'backtick';
					elseif ( $char === '-' && $next === '-' ) { $state = 'line_comment'; $i++; }
					elseif ( $char === '#' ) $state = 'line_comment';
					elseif ( $char === '/' && $next === '*' ) { $state = 'block_comment'; $i++; }
					elseif ( $char === ';' ) {
						$statement = trim( substr( $sql, $start, $i - $start ) );
						if ( $statement !== '' ) yield $statement;
						$start = $i + 1;
					}
			}
		}

		$trailing = trim( substr( $sql, $start ) );
		if ( $trailing !== '' ) {
			throw new Exception( 'Dump file does not end with a complete statement - file is truncated.' );
		}
	}
}