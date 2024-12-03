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
