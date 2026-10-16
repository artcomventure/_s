<?php

define( 'UTLTS_ACCESSIBILITY_DIRECTORY', dirname( __FILE__ ) );
define( 'UTLTS_ACCESSIBILITY_DIRECTORY_URI', UTLTS_DIRECTORY_URI . '/inc/accessibility' );

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_script( 'accessibility', UTLTS_ACCESSIBILITY_DIRECTORY_URI . '/app.js', ['behaviours', 'alter'], false, ['in_footer' => true, 'strategy' => 'defer'] );
} );