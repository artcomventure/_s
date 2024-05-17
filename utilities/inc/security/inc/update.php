<?php

add_filter( 'security_checks', function( $checks ) {
	$status = (function() {
		if ( get_core_updates( array( 'dismissed' => false ) ) ) return false;
		if ( get_site_transient( 'update_plugins' ) || get_site_transient( 'update_themes' ) ) return 'warning';
		return wp_get_translation_updates() ? 'warning' : '';
	})();

	$checks['update'] = [
		'title' => $status === true ? __( 'No updates available', 'security' ) : __( 'Updates available', 'security' ),
		'status' => $status,
		'info' => (function() use ( $updates, $status ) {
			if ( $status === true )
				return __( 'There are no updates available.', 'security' );

			if ( $status == 'warning' )
				return sprintf( __( "Updates available. <a href='%s'>Make the updates promptly</a>.", 'security' ), admin_url( 'plugins.php' ) );

			return sprintf( __( "There is a new version of WordPress available. <b>Please immediately <a href='%s'>run updates</a>!</b>", 'security' ), admin_url( 'update-core.php' ) );
		})(),
		'description' => sprintf(
			__( 'Many updates include security fixes. It is therefore important to keep WordPress itself and all plugins always up to date.%s', 'security' ),
			!wp_is_file_mod_allowed( 'capability_update_core' ) ? '<b>' . __( 'These actions are only possible in the development environment!' ) . '</b>' : ''
		)
	];

	return $checks;
} );
