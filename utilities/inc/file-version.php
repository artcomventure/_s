<?php

/**
 * Set file's timestamp as version to automatically _busting_ browser's cache.
 */
add_action( 'wp_enqueue_scripts', function () {
	$home_url  = preg_quote( get_home_url(), '/' );

	// all css and js files
	foreach ( array( wp_styles(), wp_scripts() ) as $files ) {
		foreach ( $files->registered as $file ) {
			// version already given
			if ( $file->ver ) {
				continue;
			}

			// is no local file
			if ( ! preg_match( '/^(\/|' . $home_url . ')/', $file->src ) ) {
				continue;
			}

			$file->ver = filemtime( ABSPATH . wp_make_link_relative( $file->src ) );
		}
	}
}, 12 );
