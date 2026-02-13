<?php

define( 'MEDIAELEMENT_DIRECTORY', dirname( __FILE__ ) );
define( 'MEDIAELEMENT_DIRECTORY_URI', VIDEO_DIRECTORY_URI . '/mediaelement' );

/**
 * WP loads medialementjs in `wp-includes/media.php:wp_video_shortcode()`,
 * so we check for the script in `wp_footer` action (not `wp_enqueue_scripts`).
 */
add_action( 'wp_footer', function() {
	if ( wp_script_is('wp-mediaelement' ) ) {
		wp_enqueue_script( 'video-mediaelement', MEDIAELEMENT_DIRECTORY_URI . '/app.js', ['behaviours', 'wp-mediaelement', 'mediaelement-vimeo'], false, true );
		wp_enqueue_style( 'wp-mediaelement' );
	}
} );
