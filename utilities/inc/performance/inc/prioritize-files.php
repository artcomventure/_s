<?php

/**
 * `fetchpriority` is a hint to the browser indicating how it should prioritize fetching
 * a file (img, css, js, font, iframe) relative to other files.
 */

add_filter( 'wp_preload_resources', function( $resources ) {
	// theme CSS
	$resources[] = [
		'href' => get_stylesheet_uri() . '?ver=' . get_file_version( get_stylesheet_uri() ),
		'as' => 'style',
		'type' => 'text/css',
		'fetchpriority' => 'high'
	];


	return $resources;
} );

/**
 * Preload jQuery scripts to optimize the page load.
 * Thx to https://www.crafted.at/b/wordpress-jquery-ladevorgang-optimieren/
 *
 * @since 1.20.0
 */
add_action( 'wp_enqueue_scripts', function() {
	if ( wp_script_is( 'jquery' ) ) {
		add_filter( 'wp_preload_resources', function( array $preload_resources ): array {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			// get jQuery script
			$wp_jquery_ver  = wp_scripts()->registered['jquery-core']->ver;
			$path_to_jquery = includes_url( "js/jquery/jquery$suffix.js?ver=$wp_jquery_ver" );

			$preload_resources[] = array(
				'href' => $path_to_jquery,
				'as'   => 'script',
			);

			return $preload_resources;
		} );

		if ( wp_script_is( 'jquery-migrate' ) ) {
			add_filter( 'wp_preload_resources', function( array $preload_resources ): array {
				$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

				// get jQuery migrate script
				$wp_jquery_migrate_ver  = wp_scripts()->registered['jquery-migrate']->ver;
				$path_to_jquery_migrate = includes_url( "js/jquery/jquery-migrate$suffix.js?ver=$wp_jquery_migrate_ver" );

				$preload_resources[] = array(
					'href' => $path_to_jquery_migrate,
					'as'   => 'script',
				);

				return $preload_resources;
			} );
		}
	}
} );
