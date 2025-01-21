<?php

// extend _Image Background Focus Position_ plugin
// https://www.wordpress-focalpoint.com/

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
// typo in php file name!!!
if ( !is_plugin_active( 'image-bg-position/image-bg-postion.php' ) ) return;

// tweak original callback see `wp-content/plugins/image-bg-position/image-bg-postion.php:filter_gallery_img_atts()`
// calculate position of cropped image too
remove_filter( 'wp_get_attachment_image_attributes', 'filter_gallery_img_atts', 10, 2 );
add_filter( 'wp_get_attachment_image_attributes', function( $atts, $attachment, $size ) {
	if ( $position = get_post_meta( $attachment->ID, 'bg_pos_desktop', true ) ) {
		// is image (size) cropped?
		if ( !$cropped = is_array( $size ) ) {
			$sizes = wp_get_registered_image_subsizes();
			$cropped = isset($sizes[$size]) && intval( $sizes[ $size ]['crop'] ?? '1' );
		}

		// calculate position of cropped image
		if ( $cropped ) {
			// actual image dimensions
			$image = wp_get_attachment_image_src( $attachment->ID, $size );
			list( $src, $width, $height, $resized ) = $image;

			// original dimensions
			$original = wp_get_attachment_image_src( $attachment->ID, 'full' );

			$position = explode( ' ', $position );
			$position = array_map( 'intval', $position );

			// height is cropped
			if ( $original[2] / $original[1] > $width / $height ) {
				// calculated height without cropping
				$rheight = $original[2] * $width / $original[1];
				// diff between resized original and actual height
				$diff = $rheight - $height;

				// px value of vertical position
				$position[1] = $rheight * $position[1] / 100;
				// subtract half of cropping height
				$position[1] -= $diff / 2;
				// convert back to percentage
				$position[1] = $position[1] / $height * 100;
			}
			// width is cropped
			else {
				// calculated width without cropping
				$rwidth = $original[1] * $height / $original[2];
				// diff between resized original and actual width
				$diff = $rwidth - $width;

				// px value of horizontal position
				$position[0] = $rwidth * $position[0] / 100;
				// subtract half of cropping width
				$position[0] -= $diff / 2;
				// convert back to percentage
				$position[0] = $position[0] / $width * 100;
			}

			$position = array_map( function( $percent ) {
				return round($percent, 2 ) . '%';
			}, $position );
			$position = implode( ' ', $position );
		}

		$atts['style'] = explode(';', $atts['style'] ?? '');
		$atts['style'] = array_filter( array_map( 'trim', $atts['style'] ) );

		$atts['style'][] = "object-position: {$position}";

		$atts['style'] = implode( ';', $atts['style'] );
	}

	return $atts;
}, 10, 3 );

// set position for _all_ images
add_filter( 'the_content', function( $content ) {
	// find all images
	if ( preg_match_all( '/<img[^>]+>/', $content, $images) ) {
		foreach ( $images[0] as $image ) {
			// position already defined
			if ( strpos( $image, 'object-position' ) !== false ) continue;

			// parse image attributes to array
			$atts = [];
			preg_match_all( '/ ?(\w+)="([^"]*)"/', $image, $attr );
			foreach ( $attr[0] as $key => $attribute ) {
				$atts[$attr[1][$key]] = $attr[2][$key];
			}

			// get mandatory data
			if ( !($atts['class'] ?? '') ) continue;
			if ( !preg_match( '/size-(\w+)/', $atts['class'], $size ) ) continue;
			if ( !preg_match( '/wp-image-(\d+)/', $atts['class'], $attachment ) ) continue;
			$attachment = get_post( $attachment[1] );

			// set position
			$atts = apply_filters( 'wp_get_attachment_image_attributes', $atts, $attachment, $size );

			$attributes = implode( ' ', array_map( function( $key, $value ) {
				return "{$key}=\"{$value}\"";
			}, array_keys( $atts ), array_values( $atts ) ) );

			// eventually replace image html
			$content = str_replace( $image, "<img {$attributes} />", $content );
		}
	}

	return $content;
} );
