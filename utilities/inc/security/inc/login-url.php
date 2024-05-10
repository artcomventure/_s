<?php

/**
 * Check if the WP's login url is the default `wp-login.php` one.
 */
add_filter( 'security_checks', function( $checks ) {
	$checks['login-url'] = [
		'title' => __( 'Login URL', 'security' ),
		'status' => $status = !str_ends_with(
			$login = apply_filters( 'login_url', site_url( 'wp-login.php', 'login' ), '', FALSE ),
			'wp-login.php'
		),
		'info' => $status
			? sprintf(
				__( "Your login url is <code>%s</code>", 'security' ),
				rtrim( str_replace( home_url(), '', $login ), '/' )
			)
			: __( "Your login url is WordPress' default <code>/wp-login.php</code>.", 'security' ),
		'description' => sprintf(
			__( 'Since the login page is publicly accessible it should be an unpredictable url. The default login url <code>/wp-login.php</code> is very predictable and exposes your site to hackers attempting to guess usernames and passwords.%s', 'security' ),
			!$status ? ' ' . sprintf( __( 'We recommend using the %s plugin to change it.', 'security' ), '<a href="https://wordpress.org/plugins/wps-hide-login/" target="_blank">WPS Hide Login</a>' ) : ''
		)
	];

	return $checks;
} );
