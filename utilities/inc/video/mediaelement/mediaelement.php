<?php

define( 'UTLTS_VIDEO_MEDIAELEMENT_DIRECTORY', dirname( __FILE__ ) );
define( 'UTLTS_VIDEO_MEDIAELEMENT_DIRECTORY_URI', UTLTS_VIDEO_DIRECTORY_URI . '/mediaelement' );

add_filter( 'utilities_video_players', fn( $players ) => $players + ['mediaelement' => 'MediaElement.js'] );

function utilities_video_mediaelement_enqueue(): void {
	if ( utilities_video_player() !== 'mediaelement' ) return;

	wp_enqueue_script( 'wp-mediaelement' );
	wp_enqueue_style( 'wp-mediaelement' );
}

// WP loads mediaelementjs for the shortcode only, `app.js` also covers the video block
add_filter( 'render_block_core/video', function( $content ) {
	utilities_video_mediaelement_enqueue();
	return $content;
} );

// with AJAX navigation on every page: videos may come with a page loaded via AJAX (after the SPA libs enqueued at 9–11)
add_action( 'wp_enqueue_scripts', function() {
	if ( function_exists( 'utilities_spa_enabled' ) && utilities_spa_enabled() ) utilities_video_mediaelement_enqueue();
}, 20 );

/**
 * WP loads medialementjs in `wp-includes/media.php:wp_video_shortcode()`,
 * so we check for the script in `wp_footer` action (not `wp_enqueue_scripts`).
 */
add_action( 'wp_footer', function() {
	if ( utilities_video_player() !== 'mediaelement' ) {
		wp_dequeue_script( 'wp-mediaelement' );
		wp_dequeue_script( 'mediaelement-vimeo' );
		wp_dequeue_style( 'wp-mediaelement' );
	}

	// selected player, or still true if another enqueued script depends on it → explicitly requested
	if ( wp_script_is( 'wp-mediaelement' ) ) {
		wp_enqueue_script( 'video-mediaelement', UTLTS_VIDEO_MEDIAELEMENT_DIRECTORY_URI . '/app.js', ['behaviours', 'jquery', 'wp-mediaelement', 'mediaelement-vimeo'], false, ['in_footer' => true, 'strategy' => 'defer'] );
		wp_enqueue_style( 'wp-mediaelement' );
	}
} );
