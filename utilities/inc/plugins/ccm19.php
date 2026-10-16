<?php

add_filter( 'wp_script_attributes', function( $attributes ) {
	// CCM19 integration script
	if ( ! empty( $attributes['src'] ) && preg_match( '#/(ccm19|app)\.js\?.*\bapiKey=#i', $attributes['src'] ) ) {
		$attributes['fetchpriority'] = 'high';
	}

	return $attributes;
} );
