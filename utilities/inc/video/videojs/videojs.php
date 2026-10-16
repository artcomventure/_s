<?php

define( 'UTLTS_VIDEO_VIDEOJS_DIRECTORY', dirname( __FILE__ ) );
const UTLTS_VIDEO_VIDEOJS_DIRECTORY_URI = UTLTS_VIDEO_DIRECTORY_URI . '/videojs';

add_action( 'wp_enqueue_scripts', function() {
	wp_register_script( 'videojs', UTLTS_VIDEO_VIDEOJS_DIRECTORY_URI . '/app.js', ['behaviours', 'alter', 'helpers', 'video-js'], false, ['in_footer' => true, 'strategy' => 'defer'] );

	wp_register_script( 'video-js', UTLTS_VIDEO_VIDEOJS_DIRECTORY_URI . '/video.js', [], '8.23.7', ['in_footer' => true, 'strategy' => 'defer'] );
	// auto include CSS
	if ( wp_script_is('video-js' ) ) wp_enqueue_style( 'videojs', UTLTS_VIDEO_VIDEOJS_DIRECTORY_URI . '/video-js.css' );

	if ( wp_script_is('videojs' ) ) add_filter( 'wp_video_shortcode_library', function( $lib ) {
		return 'videojs';
	} );
} );
