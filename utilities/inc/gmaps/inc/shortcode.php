<?php

add_shortcode( 'gmap', 'gmap_shortcode' );
function gmap_shortcode( $attr = [] ) {
	$atts = shortcode_atts( [
		'api_key' => '',
		'mapId' => '',
		'aspect-ratio' => ''
	], $attr, 'gmap' );

	if ( preg_match( '#(\d+)\s*/\s*(\d+)#', trim( $atts['aspect-ratio'] ), $matches ) ) {
		$aspect_ratio = ' style="--ratio: ' . (int) $matches[2] / (int) $matches[1] . '"';
	}

	ob_start();
	cookie_wall();
	$cookie_wall = ob_get_clean();

	// create block like Gutenberg would
	return '<div class="wp-block-gmap-block"' . ($aspect_ratio ?? '') . '><div class="map">' . $cookie_wall . '</div></div>';
}
