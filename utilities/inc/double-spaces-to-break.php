<?php

add_filter( 'aioseo_title', function( $title ) {
	return preg_replace( '/(<|&lt;)br ?\/?(&gt;|>)/', ' ', $title );
} );

/**
 * Convert double spaces to line breaks.
 *
 * @param $string
 * @return string
 */
function doubleSpacesToBreak( $string ) {
    $replacement = '<br />';
    if ( is_admin() || wp_is_json_request() || wp_doing_ajax() ) $replacement = ' ';

    return preg_replace( '/  +/', $replacement, $string );
}

add_filter( 'the_title', 'doubleSpacesToBreak' );