<?php

/**
 * Get string translation.
 *
 * Although we can translate strings in JS files with WordPress' `wp_set_script_translations()`
 * there are cases we can't use this functionality e.g. bogo in connection with Pjax.
 */

add_action( 'rest_api_init', function() {
	register_rest_route( 'utilities/v1', '/l10n/(?P<t9nfn>(__|_n|_n?x))', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => function( WP_REST_Request $request ) {
			switch ( $callback = $request->get_param( 't9nfn' ) ) {
				case '__':
					$t9n = $callback(
						$request['text'] ?? '',
						$request['domain'] ?? ''
					);
					break;

				case '_x':
					$t9n = $callback(
						$request['text'] ?? '',
						$request['context'] ?? '',
						$request['domain'] ?? ''
					);
					break;

				case '_n':
					$t9n = $callback(
						$request['single'] ?? '',
						$request['plural'] ?? '',
						$request['number'] ?? '',
						$request['domain'] ?? ''
					);
					break;

				case '_nx':
					$t9n = $callback(
						$request['single'] ?? '',
						$request['plural'] ?? '',
						$request['number'] ?? '',
						$request['context'] ?? '',
						$request['domain'] ?? ''
					);
					break;
			}

			return rest_ensure_response( [
				'status' => 'success',
				't9n' => $t9n ?? ''
			] );
		},
		'permission_callback' => '__return_true',
	) );
} );
