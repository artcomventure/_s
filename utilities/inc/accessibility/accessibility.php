<?php

define( 'ACCESSIBILITY_DIRECTORY', dirname( __FILE__ ) );
define( 'ACCESSIBILITY_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/inc/accessibility' );

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_script( 'accessibility', ACCESSIBILITY_DIRECTORY_URI . '/app.js', ['behaviours'], false, true );
} );