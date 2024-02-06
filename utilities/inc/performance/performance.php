<?php

add_filter( 'get_custom_logo_image_attributes', 'add_inline_svg_class', 10, 2 );
//add_filter( 'get_footer_logo_image_attributes', 'add_inline_svg_class', 10, 2 );
function add_inline_svg_class( $attr, $attachment ) {
	if ( $attachment = get_attached_file( $attachment->ID ?? $attachment ) ) {
		// svg logo as inline svg
		if ( wp_check_filetype( $attachment )['ext'] == 'svg' ) {
			if ( ! isset( $attr['class'] ) ) $attr['class'] = '';
			$attr['class'] = trim( $attr['class'] . ' inline-svg' );
		}

		// accessibility
		if ( empty($attr['aria-label']) )
			$attr['aria-label'] = sprintf( __( 'Logo %s', 'gans' ), get_bloginfo( 'name' ) );
	}

	return $attr;
}

// get svg size
add_filter( 'wp_get_attachment_image_src', function( $image, $attachment_id, $size, $icon ) {
	if ( $attachment = get_attached_file( $attachment_id ) ) {
		if ( wp_check_filetype( $attachment )['ext'] == 'svg' ) {
			if ( $svg = @simplexml_load_file( $attachment ) ) {
				$size = explode( ' ', $svg->attributes()->viewBox );

				$image[1] = $svg->attributes()->width ? $svg->attributes()->width : (int) round( $size[2] );
				$image[2] = $svg->attributes()->height ? $svg->attributes()->height : (int) round( $size[3] );
			}
		}
	}

	return $image;
}, 10, 4 );

// auto include /inc files
auto_include_files( dirname( __FILE__ ) . '/inc' );
