<?php

define( 'UTILITIES_SHORTCODES_DIRECTORY', dirname( __FILE__ ) );
const UTILITIES_SHORTCODES_DIRECTORY_URI = UTILITIES_DIRECTORY_URI . '/inc/shortcodes';

// enable shortcodes for titles
add_filter( 'the_title', 'do_shortcode', 11 );
add_filter( 'wpseo_title', 'do_shortcode', 11 );
add_filter( 'widget_title', 'do_shortcode', 11 );
add_filter( 'document_title_parts', function ( $title ) {
	if ( !is_array( $title ) ) $title = do_shortcode( $title );
	else if ( isset( $title['title'] ) ) $title['title'] = do_shortcode( $title['title'] );
	else $title = array_map( 'do_shortcode', $title );

	return $title;
}, 11 );

/**
 * Almost a duplicate of WPs `strip_shortcodes()`
 * but this function only removes the tags itself and keeps the content.
 *
 * @since 1.20.2
 */
if ( !function_exists( 'strip_shortcode_tags' ) ) {
	function strip_shortcode_tags( $content, $tags_to_remove = '' ) {
		global $shortcode_tags;

		if ( ! str_contains( $content, '[' ) ) {
			return $content;
		}

		if ( empty( $shortcode_tags ) || ! is_array( $shortcode_tags ) ) {
			return $content;
		}

		// Find all registered tag names in $content.
		preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches );

		// Either given tags or all
		if ( !is_array( $tags_to_remove ) ) $tags_to_remove = array_filter( [$tags_to_remove] );
		$tags_to_remove = $tags_to_remove ?: array_keys( $shortcode_tags );

		/**
		 * Filters the list of shortcode tags to remove from the content.
		 *
		 * @since 4.7.0
		 *
		 * @param array  $tags_to_remove Array of shortcode tags to remove.
		 * @param string $content        Content shortcodes are being removed from.
		 */
		$tags_to_remove = apply_filters( 'strip_shortcodes_tagnames', $tags_to_remove, $content );

		$tagnames = array_intersect( $tags_to_remove, $matches[1] );

		if ( empty( $tagnames ) ) {
			return $content;
		}

		$content = do_shortcodes_in_html_tags( $content, true, $tagnames );

		// in case of shortcodes with a start and end tag
		// we only remove the tags instead of the whole shortcode
		$pattern = '/\[\/?(' . implode( '|', $tagnames ) . ').*?\]/';
		$content = preg_replace( $pattern, '', $content );

		// Always restore square braces so we don't break things like <!--[if IE ]>.
		$content = unescape_invalid_shortcodes( $content );

		return $content;
	}
}

// auto include
auto_include_files( UTILITIES_SHORTCODES_DIRECTORY );
