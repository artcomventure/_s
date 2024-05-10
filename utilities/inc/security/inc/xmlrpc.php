<?php

/**
 * Disable XML-RPC.
 */
add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * ...
 */
add_filter( 'security_checks', function( $checks ) {
	include_once( ABSPATH . WPINC . '/class-IXR.php' );
	include_once( ABSPATH . WPINC . '/class-wp-http-ixr-client.php' );
	$client = new WP_HTTP_IXR_Client( home_url( 'xmlrpc.php' ) );

	if ( !$client->query( 'demo.sayHello' ) ) {
		$status = $client->getErrorCode() === -32301; // 404 error
	} else {
		$status = !( $client->getResponse() === 'Hello!' );
	}

	$checks['xmlrpc'] = [
		'title'  => __( 'XML-RPC', 'security' ),
		'status' => $status = $status ?: !apply_filters( 'xmlrpc_enabled', TRUE ),
		'info'   => ($status
			? __( "The XML-RPC-API is either not reachable or disabled.", 'security' )
			: __( "It's best to disable it altogether since it's replaced by the REST-API.", 'security' )
		),
		'description' => __( "The <code>xmlrpc.php</code> introduces security vulnerabilities and can be the target for attacks. Since it has been replaced by the REST-API it is best to disable it.", 'security' )
	];

	return $checks;
} );
