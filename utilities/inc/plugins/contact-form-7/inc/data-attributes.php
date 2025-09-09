<?php

/**
 * Add custom `data-` attributes to input tag.
 */
add_filter( 'wpcf7_form_tag', function ( $tag ) {
	$data = [];

	// get `data-` options
	foreach ( (array) $tag['options'] as $option ) {
		if ( str_starts_with( $option, 'data-' ) ) {
			$option = explode(':', $option, 2 );
			$data[$option[0]][] = $option[1] ?? '';
		}
	}

	if ( $data ) add_filter( 'wpcf7_form_elements', function ( $content ) use ( $tag, $data ) {
		return str_replace(
			" name=\"{$tag['name']}\"",
			" name=\"{$tag['name']}\" " . wpcf7_format_atts( array_map( function( $value ) {
				return is_array( $value ) ? implode( ' ', $value ) : $value;
			}, $data ) ),
			$content
		);
	} );

	return $tag;
} );