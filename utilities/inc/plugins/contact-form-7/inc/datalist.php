<?php

add_shortcode( 'datalist', function ( $attr = [] ) {
	// If shortcode is used with first attribute without key `[datalist LIST]`
	if ( $attr[0] ?? '' ) $attr['list'] = $attr[0];

	$atts = shortcode_atts( [
		'list' => '',
	], $attr, 'datalist' );

	$datalist = apply_filters( "datalist_{$atts['list']}", [] );
	$datalist = apply_filters( "datalist", $datalist, $atts['list'] );

	$datalist = array_map( function( $value ) {
		return '<option value="' . $value . '"></option>';
	}, $datalist );

	return !$datalist ? '' : '<datalist id="' . $atts['list'] . '">' . implode( '', $datalist ) . '</datalist>';
} );
