<?php

define( 'TOM_SELECT_DIRECTORY', dirname( __FILE__ ) );
const TOM_SELECT_DIRECTORY_URI = UTILITIES_DIRECTORY_URI . '/js/libs/tom-select';

// register tom-select lib
add_action( 'wp_enqueue_scripts', 'enqueue_tom_select' );
add_action( 'admin_enqueue_scripts', 'enqueue_tom_select' );
function enqueue_tom_select() {
	wp_register_script('tom-select', TOM_SELECT_DIRECTORY_URI . '/module/tom-select.complete.min.js', [], '2.5.2', true );

	// auto enqueue default styles
	if ( true || wp_script_is('tom-select' ) ) {
		wp_enqueue_style('tom-select', TOM_SELECT_DIRECTORY_URI . '/module/tom-select.min.css', [], '2.5.2' );
	}
}
