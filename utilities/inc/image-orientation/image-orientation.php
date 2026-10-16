<?php

define( 'UTLTS_IMAGEORIENTATION_DIRECTORY', dirname( __FILE__ ) );
define( 'UTLTS_IMAGEORIENTATION_DIRECTORY_URI', UTLTS_DIRECTORY_URI . '/inc/image-orientation' );

// ...
add_action( 'wp_enqueue_scripts', 'image_orientation_script' );
function image_orientation_script() {
	wp_enqueue_script( 'image-orientation', UTLTS_IMAGEORIENTATION_DIRECTORY_URI . '/app.js', array( 'behaviours' ), false, ['in_footer' => true, 'strategy' => 'defer'] );
}

/**
 * Add image orientation to image class.
 *
 * @param $attr
 * @param $attachment
 * @param $size
 *
 * @return array
 */
function image_orientation_class( $attr, $attachment, $size ) {
	if ( $image = wp_get_attachment_image_src( $attachment->ID, $size ) ) {
		list( $src, $width, $height ) = $image;

		if ( $width && $height ) {
			if ( !isset($attr['class'] ) ) $attr['class'] = [];
			else $attr['class'] = explode( ' ', $attr['class'] );

			if ( $width == $height )
				$attr['class'][] = 'ratio-square';
			elseif ( $width > $height )
				$attr['class'][] = 'ratio-landscape';
			else $attr['class'][] = 'ratio-portrait';

			$attr['class'] = implode( ' ', $attr['class'] );
		}
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'image_orientation_class', 10, 3 );
