<?php

/**
 * Shortcode for manual word breaks.
 * To make this work you have to add `hyphens: auto;` to the corresponding CSS selector.
 */

/**
 * Add soft hyphen.
 *
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

// enable shortcodes for titles
add_filter( 'the_title', 'do_shortcode' );

// remove _shy_ from document `<title>`
// TODO: only remove shy shortcode
add_filter( 'document_title_parts', function ( $title ) {
	return array_map( 'strip_shortcodes', $title );
} );

