<?php

define( 'VIDEOJS_DIRECTORY', dirname( __FILE__ ) );
const VIDEOJS_DIRECTORY_URI = VIDEO_DIRECTORY_URI . '/videojs';

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_script( 'videojs', VIDEOJS_DIRECTORY_URI . '/app.js', ['behaviours', 'video-js'], false, true );

	wp_enqueue_script( 'video-js', VIDEOJS_DIRECTORY_URI . '/video.js', [], false, true );
	// auto include CSS
	if ( wp_script_is('video-js' ) ) wp_enqueue_style( 'videojs', VIDEOJS_DIRECTORY_URI . '/video-js.css' );

	if ( wp_script_is('videojs' ) ) add_filter( 'wp_video_shortcode_library', function( $lib ) {
		return 'videojs';
	} );
} );
