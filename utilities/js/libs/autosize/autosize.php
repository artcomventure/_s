<?php

define( 'AUTOSIZE_DIRECTORY', dirname( __FILE__ ) );
define( 'AUTOSIZE_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/js/libs/autosize' );

// register pjax lib
add_action( 'wp_enqueue_scripts', function() {
	wp_register_script( 'autosize-module', AUTOSIZE_DIRECTORY_URI . '/module/autosize.min.js', [], '6.0.1', true );
	wp_register_script( 'autosize', AUTOSIZE_DIRECTORY_URI . '/app.js', ['autosize-module'], false, true );
} );
