<?php

/**
 * Display post data through shortcode.
 */

add_shortcode( 'post', function( $attr = [] ) {
	if ( isset($attr[0]) ) $attr = [ 'show' => $attr[0] ];

	$atts = shortcode_atts( array(
		'show' => '',
		'post_id' => 0
	), $attr, 'post' );

	switch ( $atts['show'] ) {
		case 'title':
			$output = get_the_title( $atts['post_id'] );
			break;
	}

	return $output ?? '';
} );

