<?php

define( 'CUSTOM_SELECT_DIRECTORY', dirname( __FILE__ ) );
define( 'CUSTOM_SELECT_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/js/libs/custom-select' );

// register pjax lib
add_action( 'wp_enqueue_scripts', function() {
	wp_register_script( 'custom-select-module', CUSTOM_SELECT_DIRECTORY_URI . '/module/custom-select.min.js', [], '1.1.15', true );
	wp_register_script( 'custom-select', CUSTOM_SELECT_DIRECTORY_URI . '/app.js', ['custom-select-module'], false, true );
} );
