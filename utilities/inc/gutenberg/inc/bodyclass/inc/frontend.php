<?php

add_filter( 'html_class', function( $classes, $css_class ) {
	// add custom html classes
	if ( $html_classes = get_post_meta( get_the_ID(), '_htmlclass', true ) ) {
		$classes = array_merge( $classes, preg_split( '#\s+#', $html_classes ) );
	}

	return $classes;
}, 10, 2 );

/**
 * Add extra body classes.
 */
add_filter( 'body_class', function ( $classes ) {
	// Page is shown on mobile device.
	if ( wp_is_mobile() ) {
		$classes[] = 'mobile-device';
	}

	// add custom body classes
	if ( $body_class = get_post_meta( get_the_ID(), '_bodyclass', true ) ) {
		$classes = array_merge( $classes, preg_split( '#\s+#', $body_class ) );
	}

    return array_filter( $classes );
} );

/**
 * Add extra post classes.
 */
add_filter( 'post_class', function( $classes, $css_class, $post_id ) {
	// add custom article classes
	if ( $article_class = get_post_meta( $post_id, '_articleclass', true ) ) {
		$classes = array_merge( $classes, preg_split( '#\s+#', $article_class ) );
	}

    return array_filter( $classes );
}, 10, 3 );