<?php

define( 'UTLTS_JS_LIBS_AIRDATEPICKER_DIRECTORY', dirname( __FILE__ ) );
define( 'UTLTS_JS_LIBS_AIRDATEPICKER_DIRECTORY_URI', UTLTS_DIRECTORY_URI . '/js/libs/air-datepicker' );

// register datepicker lib
add_action( 'wp_enqueue_scripts', function() {
    wp_register_script( 'air-datepicker', UTLTS_JS_LIBS_AIRDATEPICKER_DIRECTORY_URI . '/build/app.js', ['behaviours', 'alter'], false, ['in_footer' => true, 'strategy' => 'defer'] );
	wp_register_style( 'air-datepicker-module', UTLTS_JS_LIBS_AIRDATEPICKER_DIRECTORY_URI . '/module/air-datepicker.css', [], '3.5.0' );
} );

// add custom datepicker style/script in case pjax is enqueued
add_action( 'wp_enqueue_scripts', function() {
    if ( wp_script_is('air-datepicker' ) ) {
        wp_enqueue_style( 'air-datepicker', UTLTS_JS_LIBS_AIRDATEPICKER_DIRECTORY_URI . '/style.css', ['air-datepicker-module'] );
    }
}, 1982 );
