<?php

define( 'VIDEO_DIRECTORY', dirname( __FILE__ ) );
define( 'VIDEO_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/inc/video' );

// pass YouTube and Vimeo embeds through video shortcode
add_filter( 'embed_oembed_html', function( $html, $url, $attr, $post_id ) {
	$oEmbed = _wp_oembed_get_object();
	if ( $video = $oEmbed->fetch( $oEmbed->get_provider( $url ), $url ) ) {
		if ( in_array( $video->provider_name, ['YouTube', 'Vimeo'] ) ) {
			$html = do_shortcode( "[video src='{$url}']" );
		}
	}

	return $html;
}, 10, 4 );

// auto include files
auto_include_files( dirname( __FILE__ ) );
