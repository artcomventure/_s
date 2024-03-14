<?php

define( 'PJAX_DIRECTORY', dirname( __FILE__ ) );
define( 'PJAX_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/js/libs/pjax' );

// register pjax lib
add_action( 'wp_enqueue_scripts', function() {
	wp_register_script( 'pjax-module', PJAX_DIRECTORY_URI . '/module/pjax.min.js', [], '0.2.8', true );
	wp_register_script( 'pjax', PJAX_DIRECTORY_URI . '/app.js', ['pjax-module', 'alter'], false, true );
} );

// add custom pjax style/script in case pjax is enqueued
add_action( 'wp_enqueue_scripts', function() {
	if ( wp_script_is('pjax', 'queue' ) ) {
		wp_enqueue_style( 'pjax', PJAX_DIRECTORY_URI . '/style.css' );
		if ( $hex = get_background_color() ) {
			wp_add_inline_style( 'pjax', "body.custom-background #pjax-transition { background-color: #{$hex}; }" );
		}
	}
}, 1982 );
