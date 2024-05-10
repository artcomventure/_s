<?php

add_filter( 'security_checks', function( $checks ) {
	$updates = wp_get_update_data();

	$status = (function() use ( $updates ) {
		if ( $updates['counts']['wordpress'] ) return false;
		return !$updates['counts']['total'] ?: 'warning';
	})();

	$checks['update'] = [
		'title' => $status === true ? __( 'No updates available', 'security' ) : __( 'Updates available', 'security' ),
		'status' => $status,
		'info' => (function() use ( $updates, $status ) {
			switch ( $status ) {
				case 'warning':
					return sprintf( __( "%s available. <a href='%s'>Make the updates promptly</a>.", 'security' ), $updates['title'], admin_url( 'plugins.php' ) );

				case false:
					return sprintf( __( "There is a new version of WordPress available. <b>Please immediately <a href='%s'>run updates</a>!</b>", 'security' ), admin_url( 'update-core.php' ) );
			}

			return __( 'There are no updates available.', 'security' );
		})(),
		'description' => __( 'Many updates include security fixes. It is therefore important to keep WordPress itself and all plugins always up to date.', 'security' )
	];

	return $checks;
} );
