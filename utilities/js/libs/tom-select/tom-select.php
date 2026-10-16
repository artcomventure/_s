<?php

define( 'UTLTS_JS_LIBS_TOMSELECT_DIRECTORY', dirname( __FILE__ ) );
const UTLTS_JS_LIBS_TOMSELECT_DIRECTORY_URI = UTLTS_DIRECTORY_URI . '/js/libs/tom-select';

// register tom-select lib
add_action( 'wp_enqueue_scripts', 'enqueue_tom_select' );
add_action( 'admin_enqueue_scripts', 'enqueue_tom_select' );
function enqueue_tom_select() {
	wp_register_script('tom-select', UTLTS_JS_LIBS_TOMSELECT_DIRECTORY_URI . '/module/tom-select.complete.min.js', [], '2.5.2', ['in_footer' => true, 'strategy' => 'defer'] );

	// auto enqueue default styles
	if ( wp_script_is('tom-select' ) ) {
		wp_enqueue_style('tom-select', UTLTS_JS_LIBS_TOMSELECT_DIRECTORY_URI . '/module/tom-select.min.css', [], '2.5.2' );
	}
}
