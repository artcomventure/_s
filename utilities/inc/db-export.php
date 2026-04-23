<?php

/**
 * Export database.
 * @since 1.20.3
 */

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) die( '-1' );

add_action('admin_menu', function() {
    if ( ! user_has_role( 'administrator' ) ) return; // admins only
    if ( ! current_user_can( 'manage_options' ) ) return;

	add_management_page(
		__( 'Export Database', 'utilities' ),
		__( 'Export Database', 'utilities' ),
		'manage_options',
		'wp-db-export',
		function() {
			$upload_dir = wp_upload_dir();
			$dump_dir   = $upload_dir['basedir'] . '/sqldumps';
			$dump_url   = $upload_dir['baseurl'] . '/sqldumps';

			if ( ! file_exists( $dump_dir ) ) {
				wp_mkdir_p( $dump_dir );
			}

			// Handle Actions
			$success = null;
			$error = null;

			// 1. GET Actions (Single Delete)
			if ( isset( $_GET['action'], $_GET['file'], $_GET['_wpnonce'] ) && $_GET['action'] === 'delete' ) {
				$file_to_delete = sanitize_file_name( $_GET['file'] );
				if ( wp_verify_nonce( $_GET['_wpnonce'], 'delete_dump_' . $file_to_delete ) ) {
					$file_path = $dump_dir . '/' . $file_to_delete;
					if ( file_exists( $file_path ) ) {
						unlink( $file_path );
						$success = sprintf( __( 'File "%s" deleted.', 'utilities' ), $file_to_delete );
					} else {
						$error = __( 'File not found.', 'utilities' );
					}
				} else {
					$error = __( 'Invalid nonce.', 'utilities' );
				}
			}

			// 2. POST Actions (Export & Bulk Delete)
			if ( $_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer( 'wp_db_export_action' ) ) {

				// Bulk Delete
				if ( isset( $_POST['bulk_action'], $_POST['bulk_files'] ) && $_POST['bulk_action'] === 'delete' ) {
					$count = 0;

                    if ( is_array( $_POST['bulk_files'] ) ) {
						foreach ( $_POST['bulk_files'] as $file_name ) {
							$file_path = $dump_dir . '/' . sanitize_file_name( $file_name );
							if ( file_exists( $file_path ) ) {
								unlink( $file_path );
								$count++;
							}
						}
					}

                    if ( $count === 1 ) {
                        $success = sprintf( __( 'File "%s" deleted.', 'utilities' ), sanitize_file_name( $file_name ) );;
                    } elseif ( $count > 0 ) {
						$success = sprintf( __( '%d files deleted.', 'utilities' ), $count );
					}
				}

				// Export Database
				if ( isset( $_POST['export_database'] ) ) {
					global $wpdb;
					set_time_limit(0);

                    $filename = sanitize_file_name( get_bloginfo( 'name' ) );
                    $filename .= date('-Ymd-U') . '.sql';
					$export_path = $dump_dir . '/' . $filename;
					$f = fopen( $export_path, 'w' );

					if ( $f ) {
                        $wpdb->query( "SET NAMES 'utf8mb4'" );
                        fwrite( $f, "SET NAMES utf8mb4;\n\n" );
						$tables = $wpdb->get_col( 'SHOW TABLES' );
						foreach ( $tables as $table ) {
							// Structure
							fwrite( $f, "DROP TABLE IF EXISTS `$table`;\n" );
							$create_table = $wpdb->get_var( "SHOW CREATE TABLE `$table`", 1 );
							fwrite( $f, $create_table . ";\n\n" );

							// Data
							$limit = 1000;
							$offset = 0;
							while ( true ) {
								$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `$table` LIMIT %d OFFSET %d", $limit, $offset ), ARRAY_A );
								if ( empty( $rows ) ) break;

								foreach ( $rows as $row ) {
									$keys = array_keys( $row );
									$values = array_map( function( $v ) {
										if ( is_null( $v ) ) return 'NULL';
										return "'" . esc_sql( $v ) . "'";
									}, array_values( $row ) );

									fwrite( $f, "INSERT INTO `$table` (`" . implode( '`, `', $keys ) . "`) VALUES (" . implode( ', ', $values ) . ");\n" );
								}
								$offset += $limit;
							}
							fwrite( $f, "\n" );
						}
						fclose( $f );

						$success = sprintf(
							__( 'Database successfully exported: %s', 'utilities' ),
							'<a href="' . esc_url($dump_url . '/' . $filename) . '" download>' . "$filename</a>"
						);
					} else {
						$error = __( 'Error creating export file.', 'utilities' );
					}
				}
			}

			// Get Files
			$files = glob( $dump_dir . '/*.sql' );
			if ( $files ) {
				usort( $files, function( $a, $b ) {
					return filemtime( $b ) - filemtime( $a );
				});
			} else {
				$files = [];
			}
			?>
			<div class="wrap">
				<h1><?php _e( 'Export Database', 'utilities' ) ?></h1>

				<?php if ( isset($success) || isset($error) ) : ?>
					<div class="<?php echo isset($success) ? 'updated' : 'error' ?> notice is-dismissible">
						<p><?php echo isset($success) ? $success : esc_html($error); ?></p>
					</div>
				<?php endif; ?>

                <p class="desription">
                    <?php _e( 'If the database dump is not a backup, please delete it immediately after downloading.', 'utilities' ) ?>
                </p>

				<form method="post" action="">
					<?php wp_nonce_field( 'wp_db_export_action' ); ?>

					<div class="tablenav top">
						<div class="alignleft actions bulkactions">
							<select name="bulk_action">
								<option value="-1"><?php _e( 'Bulk actions' ); ?></option>
								<option value="delete"><?php _e( 'Delete' ); ?></option>
							</select>
							<input type="submit" class="button action" value="<?php _e( 'Apply' ); ?>">
						</div>
                        <div class="alignleft actions">
                            <input type="submit" name="export_database" id="export-db-btn" class="button button-primary" value="<?php _e( 'New database export', 'utilities' ) ?>" onclick="this.nextElementSibling.classList.add('is-active')">
                            <span class="spinner" id="export-db-spinner" style="float: none; margin-top: 5px;"></span>
                        </div>
					</div>

					<table class="widefat striped">
						<thead>
							<tr>
								<td id="cb" class="manage-column column-cb check-column"><input type="checkbox"></td>
								<th scope="col" class="manage-column column-primary"><?php _ex( 'File', 'column name'); ?></th>
								<th scope="col" class="manage-column"><?php _e('Date'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( empty($files) ) : ?>
								<tr><td colspan="3"><?php _e('No exports found.', 'utilities'); ?></td></tr>
							<?php else : ?>
								<?php foreach ( $files as $file ) :
									$filename = basename($file);
									$url = $dump_url . '/' . $filename;
									$date = sprintf(
                                        __( '%1$s at %2$s' ),
                                        date_i18n( __( 'Y/m/d' ), filemtime( $file ) ),
                                        date_i18n( __( 'g:i a' ), filemtime( $file ) )
                                    );
									$delete_url = wp_nonce_url( add_query_arg( ['page' => 'wp-db-export', 'action' => 'delete', 'file' => $filename], admin_url( 'tools.php' ) ), 'delete_dump_' . $filename );
								?>
								<tr>
									<th scope="row" class="check-column">
										<input type="checkbox" name="bulk_files[]" value="<?php echo esc_attr($filename); ?>">
									</th>
									<td class="column-primary">
										<strong><a href="<?php echo esc_url($url); ?>" download><?php echo esc_html($filename); ?></a></strong>
										<div class="row-actions">
											<span class="download">
												<a href="<?php echo esc_url($url); ?>" download aria-label="<?php echo esc_attr( sprintf( __( 'Download %s', 'utilities' ), $filename ) ); ?>"><?php _e( 'Download', 'utilities' ); ?></a> |
											</span>
											<span class="trash">
												<a href="<?php echo esc_url($delete_url); ?>" class="submitdelete" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this export?', 'utilities' ) ); ?>')" aria-label="<?php echo esc_attr( sprintf( __( 'Delete %s', 'utilities' ), $filename ) ); ?>"><?php _e( 'Delete' ); ?></a>
											</span>
										</div>
									</td>
									<td style="width: 14%"><?php echo esc_html($date); ?></td>
								</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</form>
			</div>
			<?php
		}
	);
} );
