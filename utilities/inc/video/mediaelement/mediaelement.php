<?php

define( 'UTLTS_VIDEO_MEDIAELEMENT_DIRECTORY', dirname( __FILE__ ) );
define( 'UTLTS_VIDEO_MEDIAELEMENT_DIRECTORY_URI', UTLTS_VIDEO_DIRECTORY_URI . '/mediaelement' );

/**
 * WP loads medialementjs in `wp-includes/media.php:wp_video_shortcode()`,
 * so we check for the script in `wp_footer` action (not `wp_enqueue_scripts`).
 */
add_action( 'wp_footer', function() {
	wp_dequeue_script( 'wp-mediaelement' );
	wp_dequeue_script( 'mediaelement-vimeo' );
	wp_dequeue_style( 'wp-mediaelement' );

	// still true if another enqueued script depends on it → explicitly requested
	if ( wp_script_is('wp-mediaelement' ) ) {
		wp_enqueue_script( 'video-mediaelement', UTLTS_VIDEO_MEDIAELEMENT_DIRECTORY_URI . '/app.js', ['behaviours', 'jquery', 'wp-mediaelement', 'mediaelement-vimeo'], false, ['in_footer' => true, 'strategy' => 'defer'] );
		wp_enqueue_style( 'wp-mediaelement' );
	}
} );
