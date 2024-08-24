<?php

define( 'VIDEO_DIRECTORY', dirname( __FILE__ ) );
define( 'VIDEO_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/inc/video' );

/**
 * Enqueue frontend scripts and styles.
 */
add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_script( 'utilities-video', VIDEO_DIRECTORY_URI . '/app.js', [], false, true );
	wp_enqueue_style( 'utilities-video', VIDEO_DIRECTORY_URI . '/video.css' );
	wp_add_inline_style( 'utilities-video', '.video-html::before {
	background-image: url(' . includes_url( '/js/mediaelement/mejs-controls.svg' ) . ');
}' );
} );

/**
 * Override WPs video shortcode.
 */

add_filter( 'embed_oembed_html', 'video__embed_oembed_html', 10, 4 );
function video__embed_oembed_html( $html, $url, $attr, $post_id ) {
    $oEmbed = _wp_oembed_get_object();
    if ( $video = $oEmbed->fetch( $oEmbed->get_provider( $url ), $url ) ) {
        if ( $video->provider_name == 'YouTube' ) {
            // make video autoplay
            $video->html = preg_replace( '/src="([^"|^\?]+)(\?([^"]*))?"/', 'src="$1?autoplay=1&rel=0&$3"', $video->html );
			// change preview image to high-res
            $video->thumbnail_url = preg_replace( '/[^\/]+\.jpg$/', 'maxresdefault.jpg', $video->thumbnail_url );
            // activate extended data protection mode
            $video->html = str_replace( $video->provider_url, 'https://www.youtube-nocookie.com/', $video->html );
        }

		$label = sprintf( __( 'Play video "%s"', 'video' ), $video->title );

        $html = '<div class="video-html" role="button" tabindex="0" aria-label=\'' . $label . '\' style="background-image: url(' . $video->thumbnail_url . ');">'
                . esc_html( trim( $video->html ) )
                . '</div>';

//        $html = '<div class="video-canvas" role="button" tabindex="0" aria-label=\'' . $label . '\' style="background-image: url(' . $video->thumbnail_url . ');"><pre>'
//                . substr( preg_replace( '/<\/iframe>$/', '', trim( $video->html ) ), 1, -1 ) . '</pre>'
//                . '</div>';
    }

    return $html;
}

// auto include files
auto_include_files( dirname( __FILE__ ) );
