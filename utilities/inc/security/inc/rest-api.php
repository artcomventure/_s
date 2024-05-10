<?php

const rest_endpoints_to_disable = ['/wp-json/wp/v2/users'];

/**
 * Disable certain (or all) REST-API endpoints for unauthenticated users.
 */
add_filter( 'rest_authentication_errors', function( $result ) {
	if ( ! is_user_logged_in() ) {
		// endpoints to disabled (leave blank for "all")
		// list of possible WP endpoints: https://developer.wordpress.org/rest-api/reference/
		$routes = implode( '|', rest_endpoints_to_disable );

		// check $routes against current request uri
		if ( empty( $routes ) || preg_match( "#^($routes)($|/)#", $_SERVER['REQUEST_URI'] ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You are not allowed to make REST-API requests to this route.', 'security' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}
	}

	return $result;
} );

/**
 * ...
 */
add_filter( 'security_checks', function( $checks ) {
	$checks['rest-api'] = [
		'title' => __( 'Rest-API', 'security' ),
		'status' => true,
		'info' => sprintf(
			__( "Critical endpoints are disabled for non-authenticated users: %s", 'security' ),
			'<ul><li>' . implode( '</li><li>', rest_endpoints_to_disable ) . '</li></ul>'
		),
		'description' => __( "Some REST-API endpoints contains vulnerable data (e.g. usernames) of your site which hackers can use to start their attacks.", 'security' )
	];

	return $checks;
} );
