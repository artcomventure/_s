<?php

/**
 * Check if the database table prefix is WP's default `wp_`.
 */
add_filter( 'security_checks', function( $checks ) {
	global $wpdb;

	$checks['db-table-prefix'] = [
		'title' => __( 'DB table prefix', 'security' ),
		'status' => $status = ($wpdb->prefix !== 'wp_' ? true : 'warning'),
		'info' => $status === true
			? sprintf(
				__( "Your database table prefix is <code>%s</code>", 'security' ),
				$wpdb->prefix
			)
			: __( "Your database table prefix is WordPress' default <code>wp_</code>", 'security' ),
		'description' => sprintf (
			__( "To prevent SQL injections (or at least make it more difficult) the database table prefix should not be WordPress' default <code>wp_</code> prefix. We recommend using the %s plugin to change that. Once changed you can deactivate and uninstall the plugin again.", 'security' ),
			'<a href="https://wordpress.org/plugins/brozzme-db-prefix-change/" target="_blank">Brozzme DB Prefix</a>'
		)
	];

	return $checks;
} );
