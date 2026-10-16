<?php

define( 'UTLTS_PERFORMANCE_DIRECTORY', dirname( __FILE__ ) );
const UTLTS_PERFORMANCE_DIRECTORY_URI = UTLTS_DIRECTORY_URI . '/inc/performance';

/**
 * Is attachment an SVG?
 * Mime type stored on upload (`wp_check_filetype()` depends on the current user's allowed mime types).
 */
function is_svg_attachment( $attachment_id ): bool {
	return get_post_mime_type( $attachment_id ) === 'image/svg+xml';
}

add_filter( 'get_custom_logo_image_attributes', 'add_inline_svg_class', 10, 2 );
function add_inline_svg_class( $attr, $attachment ) {
	// svg logo as inline svg
	if ( is_svg_attachment( $attachment->ID ?? $attachment ) ) {
		if ( ! isset( $attr['class'] ) ) $attr['class'] = '';
		$attr['class'] = trim( $attr['class'] . ' inline-svg' );

		$attr['role'] = 'img';

		// accessibility
		if ( empty($attr['aria-label']) )
			$attr['aria-label'] = get_post_meta( $attachment->ID ?? $attachment, '_wp_attachment_image_alt', true )
				?: sprintf( __( 'Logo %s, Link to home page', 'utilities' ), get_bloginfo( 'name' ) );
	}

	return $attr;
}

/**
 * Get SVG dimensions from `width`/`height` (unitless or px) and/or `viewBox`.
 *
 * @return int[]|null [width, height]
 */
function get_svg_dimensions( string $file ): ?array {
	if ( !$svg = @simplexml_load_file( $file ) ) return null;
	$attr = $svg->attributes();

	// unitless or px only (`%`, `em` etc. can't be resolved)
	$length = fn( $value ) => preg_match( '/^\s*(\d+(?:\.\d+)?)\s*(px)?\s*$/i', (string) $value, $m ) ? (int) round( $m[1] ) : 0;
	$width = $length( $attr->width );
	$height = $length( $attr->height );

	// `viewBox` may be separated by spaces and/or commas
	$box = preg_split( '/[\s,]+/', trim( (string) $attr->viewBox ) );
	$ratio = count( $box ) === 4 && (float) $box[2] > 0 ? (float) $box[3] / (float) $box[2] : 0;

	if ( $ratio ) {
		if ( !$width && !$height ) [ $width, $height ] = [ (int) round( (float) $box[2] ), (int) round( (float) $box[3] ) ];
		elseif ( !$height ) $height = (int) round( $width * $ratio );
		elseif ( !$width ) $width = (int) round( $height / $ratio );
	}

	return $width && $height ? [ $width, $height ] : null;
}

/**
 * Get SVG attachment's dimensions, parsed once and stored in its metadata.
 *
 * @return int[]|null [width, height]
 */
function get_svg_attachment_size( int $attachment_id ): ?array {
	$meta = wp_get_attachment_metadata( $attachment_id );
	$meta = is_array( $meta ) ? $meta : [];

	// `0` means unknown (see `wp_get_attachment_metadata` filter in `media.php`)
	if ( empty( $meta['width'] ) || empty( $meta['height'] ) ) {
		// already checked: unreadable SVG, don't parse again
		if ( !empty( $meta['svg_size_checked'] ) ) return null;

		$size = ( $file = get_attached_file( $attachment_id ) ) ? get_svg_dimensions( $file ) : null;

		$meta['width'] = $size[0] ?? 0;
		$meta['height'] = $size[1] ?? 0;
		$meta['svg_size_checked'] = true;
		wp_update_attachment_metadata( $attachment_id, $meta );
	}

	return $meta['width'] && $meta['height'] ? [ (int) $meta['width'], (int) $meta['height'] ] : null;
}

// get svg size, constrained to the requested image size like WP does for raster images
add_filter( 'wp_get_attachment_image_src', function( $image, $attachment_id, $size ) {
	if ( !$image || !is_svg_attachment( $attachment_id ) ) return $image;

	if ( $dimensions = get_svg_attachment_size( $attachment_id ) )
		[ $image[1], $image[2] ] = image_constrain_size_for_editor( $dimensions[0], $dimensions[1], $size );

	return $image;
}, 10, 3 );

// auto include /inc files
auto_include_files( dirname( __FILE__ ) . '/inc' );
