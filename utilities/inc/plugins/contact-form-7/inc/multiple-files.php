<?php

/**
 * Update file input for multiple files.
 */
add_filter( 'wpcf7_form_tag', function ( $tag ) {
	if ( 'file' === $tag['basetype'] ) { // file fields only
		// check for a `multiple` option
		foreach ( (array) $tag['options'] as $option ) {
			if ( str_starts_with( $option, 'multiple' ) ) {
				// make field name an array and add `multiple` attribute
				add_filter( 'wpcf7_form_elements', function ( $content ) use ( $tag ) {
					return str_replace( " name=\"{$tag['name']}\"", " name=\"{$tag['name']}[]\" multiple=\"multiple\"", $content );
				} );

				break;
			}
		}
	}

	return $tag;
} );
