<?php

/**
 * Add extra body classes.
 */
add_filter( 'body_class', function ( $classes ) {
	// Page is shown on mobile device.
	if ( wp_is_mobile() ) {
		$classes[] = 'mobile';
	}

	// add custom body classes
	$classes[] = get_post_meta( get_the_ID(), '_bodyclass', true );

    return $classes;
} );