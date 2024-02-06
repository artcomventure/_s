<?php

/**
 * Shortcode for manual input of `&nbsp;`.
 * 
 * @return string
 */
add_shortcode( 'nbsp', function() {
	if ( is_admin() || wp_is_json_request() ) {
		return '';
	}

	return '&nbsp;';
} );

