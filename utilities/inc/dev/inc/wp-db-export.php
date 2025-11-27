<?php

// This approach will not be pursued further for now.
// @see https://code.jivo.group/wordpressassets/sqldumps
return;

add_action('admin_menu', function() {
	add_management_page(
		__( 'Export Database', 'utilities' ),
		__( 'Export Database', 'utilities' ),
		'manage_options',
		'wp-db-export',
		function() {
			if ( ! current_user_can( 'manage_options' ) ) return;

			// eventually export database
			if ( isset( $_POST['export_database'] ) && check_admin_referer( 'export_database_action' ) ) {
				$command = 'wp';

				$filename = 'wp-db-' . date('Ymd_His') . '.sql';

				$upload_dir = wp_upload_dir();
				$export_path = "/dbdumps/$filename";

				$command = "$command --path=" . ABSPATH . " db export {$upload_dir['basedir']}$export_path 2>&1";
				$output = shell_exec( $command );

				if ( file_exists( $export_path ) ) {
					$url = $upload_dir['baseurl'] . "/dbdumps/$filename";
					$success = sprintf(
						__( 'Database successfully exported: %s', 'utilities' ),
						'<a href="' . $url . '" download>' . $url . '</a>'
					);
				} else {
					$error = __( 'Error exporting the database.', 'utilities' );
					$error .= '<pre>' . esc_html($output) . '</pre>';
				}
			}
			?>
			<div class="wrap">
				<h1><?php _e( 'Export Database', 'utilities' ) ?></h1>

				<?php if ( $success ?? $error ?? '' ) : ?>
					<div class="<?php echo isset($success) ? 'notice' : 'error is-dismissible' ?> notice-info">
						<p><?php echo $success ?? $error; ?></p>
					</div>
				<?php endif; ?>

				<form method="post" action="">
					<?php wp_nonce_field( 'export_database_action' ); ?>
					<p>
						<input type="submit" name="export_database" class="button button-primary" value="<?php _e( 'Export Database now', 'utilities' ) ?>">
					</p>
				</form>
			</div>
			<?php
		}
	);
} );