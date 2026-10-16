<?php

// REST routes (without `/wp-json` prefix), subroutes included
const SECURITY_REST_ROUTES_TO_DISABLE = ['/wp/v2/users'];

/**
 * Disable certain (or all) REST-API endpoints for unauthenticated users.
 * Checks the resolved route: also covers `?rest_route=`, subdirectory installs and embeds.
 */
add_filter( 'rest_pre_dispatch', function( $result, $server, $request ) {
	if ( is_user_logged_in() ) return $result;

	// endpoints to disabled (leave blank for "all")
	// list of possible WP endpoints: https://developer.wordpress.org/rest-api/reference/
	$routes = implode( '|', array_map( fn( $route ) => preg_quote( $route, '#' ), SECURITY_REST_ROUTES_TO_DISABLE ) );

	if ( empty( $routes ) || preg_match( "#^($routes)($|/)#i", untrailingslashit( $request->get_route() ) ) ) {
		return new WP_Error(
			'rest_forbidden',
			__( 'You are not allowed to make REST-API requests to this route.', 'security' ),
			[ 'status' => rest_authorization_required_code() ]
		);
	}

	return $result;
}, 10, 3 );

/**
 * ...
 */
add_filter( 'security_checks', function( $checks ) {
	$checks['rest-api'] = [
		'title' => __( 'Rest-API', 'security' ),
		'status' => true,
		'info' => sprintf(
			__( "Critical endpoints are disabled for non-authenticated users: %s", 'security' ),
			'<ul><li>' . implode( '</li><li>', SECURITY_REST_ROUTES_TO_DISABLE ) . '</li></ul>'
		),
		'description' => __( "Some REST-API endpoints contains vulnerable data (e.g. usernames) of your site which hackers can use to start their attacks.", 'security' )
	];

	return $checks;
} );
