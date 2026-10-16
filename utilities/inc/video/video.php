<?php

define( 'UTLTS_VIDEO_DIRECTORY', dirname( __FILE__ ) );
define( 'UTLTS_VIDEO_DIRECTORY_URI', UTLTS_DIRECTORY_URI . '/inc/video' );

/**
 * Video provider (`youtube`/`vimeo`) of an oEmbed URL, without HTTP request.
 *
 * @since 1.21.0
 */
function get_video_provider( string $url ): ?string {
	$endpoint = $url ? _wp_oembed_get_object()->get_provider( $url, ['discover' => false] ) : false;
	if ( !$endpoint ) return null;

	$host = wp_parse_url( $endpoint, PHP_URL_HOST );
	foreach ( ['youtube', 'vimeo'] as $provider ) {
		if ( preg_match( "/(^|\.)$provider\.com$/", $host ) ) return $provider;
	}

	return null;
}

/**
 * Video shortcode to oembed.
 *
 * @since 1.20.7
 */
add_filter( 'wp_video_shortcode', function( $output, $atts ) {
	if ( !$provider = get_video_provider( $atts['src'] ?? '' ) ) return $output;

	// cached by WP_Embed
	global $wp_embed;
	if ( !$iframe = $wp_embed->shortcode( [], $atts['src'] ) ) return $output;

	// failed fetch returns a plain link
	$tags = new WP_HTML_Tag_Processor( $iframe );
	if ( !$tags->next_tag( 'iframe' ) ) return $output;

	$classes = "wp-block-embed is-type-video is-provider-$provider wp-block-embed-$provider";

	// aspect ratio from iframe dimensions
	$width  = (int) $tags->get_attribute( 'width' );
	$height = (int) $tags->get_attribute( 'height' );
	if ( $width && $height && abs( round( $width / $height, 2 ) - 1.78 ) < 0.1 ) {
		$classes .= ' wp-embed-aspect-16-9 wp-has-aspect-ratio';
	}

	return "<figure class=\"$classes\"><div class=\"wp-block-embed__wrapper\">$iframe</div></figure>";
}, 10, 2 );

/**
 * Video without `width`/`height` in shortcode: attachment dimensions, else `$content_width` in 16:9
 * (instead of WP's fixed 640 × 360). Content width limits it via CSS.
 *
 * @since 1.21.0
 */
add_filter( 'shortcode_atts_video', function( $out, $pairs, $atts ) {
	if ( isset( $atts['width'] ) || isset( $atts['height'] ) ) return $out;

	foreach ( array_merge( ['src'], wp_get_video_extensions() ) as $key ) {
		if ( empty( $out[ $key ] ) ) continue;

		$meta = wp_get_attachment_metadata( attachment_url_to_postid( $out[ $key ] ) );
		if ( !empty( $meta['width'] ) && !empty( $meta['height'] ) ) {
			$out['width']  = (int) $meta['width'];
			$out['height'] = (int) $meta['height'];

			return $out;
		}

		break;
	}

	if ( !empty( $GLOBALS['content_width'] ) ) {
		$out['width']  = (int) $GLOBALS['content_width'];
		$out['height'] = (int) round( $out['width'] * 9 / 16 );
	}

	return $out;
}, 10, 3 );

// auto include files
auto_include_files( dirname( __FILE__ ) );
