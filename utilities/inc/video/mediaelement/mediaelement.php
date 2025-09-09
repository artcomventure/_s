<?php

define( 'MEDIAELEMENT_DIRECTORY', dirname( __FILE__ ) );
define( 'MEDIAELEMENT_DIRECTORY_URI', VIDEO_DIRECTORY_URI . '/mediaelement' );

add_action( 'wp_enqueue_scripts', function() {
	if ( wp_script_is('wp-mediaelement' ) ) {
		wp_enqueue_script( 'video-mediaelement', MEDIAELEMENT_DIRECTORY_URI . '/app.js', ['alter'], false, true );
	}
} );
