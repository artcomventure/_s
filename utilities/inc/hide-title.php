<?php

/**
 * Removes '!' at first position of title which indicates hidden title.
 *
 * @param string $title
 *
 * @return string
 */
function remove_hide_title_indicator( string $title ): string {
	return ltrim( $title, '!' );
}

// ---
// WP

add_filter( 'the_title', 'remove_hide_title_indicator' );

add_filter( 'document_title_parts', function ( $title ) {
	if ( !is_array( $title ) ) $title = remove_hide_title_indicator( $title );
	else if ( isset( $title['title'] ) ) $title['title'] = remove_hide_title_indicator( $title['title'] );
	else $title = array_map( 'remove_hide_title_indicator', array_map( function( $part ) {
		return $part ?: ''; // mustn't be `null`
	}, $title ) );

	return $title;
} );

// ---
// plugin: autodescription

add_filter( 'the_seo_framework_pre_get_document_title', 'remove_hide_title_indicator' );

add_filter( 'the_seo_framework_meta_render_data', function( $data ) {
	foreach ( $data as $property => $content ) {
		if ( !str_contains( $property, ':title' ) ) continue;
		$data[$property]['attributes']['content'] = remove_hide_title_indicator( $content['attributes']['content'] );
	}

	return $data;
} );

add_filter( 'the_seo_framework_schema_graph_data', function ( $graph ) {
	function _remove_hide_title_indicator( $data ) {
		foreach ( $data as $i => $value ) {
			if ( is_array( $value ) ) $data[$i] = _remove_hide_title_indicator( $value );
			else if ( $i == 'name' ) {
				$data[$i] = remove_hide_title_indicator( $value );
			}
		}

		return $data;
	}

	return _remove_hide_title_indicator( $graph );
} );

// ---
// plugin: Yoast

add_filter( 'wpseo_title', 'remove_hide_title_indicator' );

/**
 * Add `.hide-entry-title` class to post classes.
 * To actually hide the title add corresponding CSS.
 */
add_filter( 'post_class', function( $classes, $post_id ) {
	if ( ($post = get_post( $post_id )) && $post->post_title && $post->post_title[0] == '!' )
		$classes[] = 'hide-entry-title';

	return $classes;
}, 10, 2 );