<?php

define( 'VIDEO_DIRECTORY', dirname( __FILE__ ) );
define( 'VIDEO_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/inc/video' );

/**
 * Video shortcode to oembed.
 *
 * @since 1.20.7
 */
add_filter( 'wp_video_shortcode', function( $output, $atts ) {
	$oEmbed = _wp_oembed_get_object();
	if ( $video = $oEmbed->fetch( $oEmbed->get_provider( $atts['src'] ), $atts['src'] ) ) {
		if ( in_array( $video->provider_name, ['YouTube', 'Vimeo'] ) ) {
			global $wp_embed;

			if ( $iframe = $wp_embed->shortcode( [], $atts['src'] ) ) {
				$provider_slug = sanitize_title( $video->provider_name );
				$type          = $video->type ?? 'video';
				$classes       = "wp-block-embed is-type-$type is-provider-$provider_slug wp-block-embed-$provider_slug";

				if ( isset( $video->width, $video->height ) && $video->height ) {
					$ratio = round( $video->width / $video->height, 2 );
					if ( abs( $ratio - 1.78 ) < 0.1 ) {
						$classes .= ' wp-embed-aspect-16-9 wp-has-aspect-ratio';
					}
				}

				$output = "<figure class=\"$classes\"><div class=\"wp-block-embed__wrapper\">$iframe</div></figure>";
			}
		}
	}

	return $output;
}, 10, 2 );

// auto include files
auto_include_files( dirname( __FILE__ ) );
