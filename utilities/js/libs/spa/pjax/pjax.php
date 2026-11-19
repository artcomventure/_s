<?php

define( 'UTLTS_JS_LIBS_SPA_PJAX_DIRECTORY', dirname( __FILE__ ) );
const UTLTS_JS_LIBS_SPA_PJAX_DIRECTORY_URI = UTLTS_DIRECTORY_URI . '/js/libs/spa/pjax';

// register pjax lib
add_action( 'wp_enqueue_scripts', function() {
	wp_register_script( 'pjax-module', UTLTS_JS_LIBS_SPA_PJAX_DIRECTORY_URI . '/module/pjax.min.js', [], '0.2.8', ['in_footer' => true, 'strategy' => 'defer'] );
	wp_register_script( 'pjax', UTLTS_JS_LIBS_SPA_PJAX_DIRECTORY_URI . '/app.js', ['pjax-module', 'behaviours', 'alter'], false, ['in_footer' => true, 'strategy' => 'defer'] );

	if ( get_option( 'utlts_spa' ) )
		wp_enqueue_script( 'pjax' );
}, 9 );

// make sure `core-block-supports-inline-css.css` is enqueued (pjax switches it)
add_action( 'wp_footer', function() {
	if ( wp_script_is( 'pjax' ) && !isset( wp_styles()->registered['core-block-supports'] ) ) {
		wp_register_style( 'core-block-supports', false );
		wp_add_inline_style( 'core-block-supports', "/* make sure core-block-supports-inline-css is enqueued */" );
		wp_enqueue_style( 'core-block-supports' );
	}
} );
