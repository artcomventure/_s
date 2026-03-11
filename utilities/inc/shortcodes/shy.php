<?php

/**
 * Add soft hyphen.
 * @return string
 */
function shy_shortcode() {
	if ( is_admin() || wp_is_json_request() ) {
		return '';
	}

	return '&shy;';
}

add_shortcode( 'shy', 'shy_shortcode' );
add_shortcode( '-', 'shy_shortcode' );

// remove shy shortcode from document `<title>`
// @since 1.20.2
add_filter( 'document_title_parts', function ( $title ) {
	if ( !is_array( $title ) ) $title = strip_shortcode_tags( $title, ['shy', '-'] );
	else if ( isset( $title['title'] ) ) $title['title'] = strip_shortcode_tags( $title['title'], ['shy', '-'] );
	else $title = array_map( 'strip_shortcode_tags', $title, ['shy', '-'] );

	return $title;
} );

// Yoast
// @since 1.20.2
add_filter( 'wpseo_title', function( $title ) {
	return strip_shortcode_tags( $title, ['shy', '-'] );
} );

