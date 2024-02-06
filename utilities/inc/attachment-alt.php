<?php

/**
 * Add a default image alt (attachment title).
 */

add_filter( 'wp_get_attachment_image_attributes', function( $attr, $attachment ) {
	// don't override existing alt text
	if ( empty( $attr['alt'] ) ) {
		$attr['alt'] = get_the_title( $attachment );
	}

	return $attr;
}, 10, 2 );