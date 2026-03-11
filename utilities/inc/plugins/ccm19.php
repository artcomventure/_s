<?php

add_filter( 'wp_script_attributes', function( $attributes ) {
	if ( $code = get_site_option( 'ccm19_code' ) ) {
		if ( preg_match( '/ src=([\'"])((?>[^"\'?#]|(?!\1)["\'])*\/(ccm19|app)\.js\?(?>[^"\']|(?!\1).)*)\1/i', $code, $match ) ) {
			if ( $attributes['src'] == $match[2] ) {
				$attributes['fetchpriority'] = 'high';
			}
		}

	}

	return $attributes;
} );
