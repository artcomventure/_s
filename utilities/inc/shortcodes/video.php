<?php

add_theme_support( 'responsive-embeds' );

// ---
// Override WPs video shortcode.

add_filter( 'embed_oembed_html', 'video__embed_oembed_html', 10, 4 );
function video__embed_oembed_html( $html, $url, $attr, $post_id ) {
    $oEmbed = _wp_oembed_get_object();
    if ( $video = $oEmbed->fetch( $oEmbed->get_provider( $url ), $url ) ) {
        // make video autoplay
        $video->html = preg_replace( '/src="([^"|^\?]+)(\?([^"]*))?"/', 'src="$1?autoplay=1&rel=0&$3"', $video->html );

//		$video->thumbnail_url = preg_replace( '/[^\/]+\.jpg$/', 'maxresdefault.jpg', $video->thumbnail_url );

        $html = '<pre>' . substr( preg_replace( '/<\/iframe>$/', '', trim( $video->html ) ), 1, -1 ) . '</pre>';
    }

    return $html;
}
