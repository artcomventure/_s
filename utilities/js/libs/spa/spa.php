<?php

/**
 * JavaScript libraries for a single page application (SPA).
 */

define( 'UTLTS_JS_LIBS_SPA_DIRECTORY', dirname( __FILE__ ) );
const UTLTS_JS_LIBS_SPA_DIRECTORY_URI = UTILITIES_DIRECTORY_URI . '/js/libs/spa';

add_filter( 'should_load_separate_core_block_assets', '__return_false' );
//add_filter( 'should_load_block_assets_on_demand', '__return_false' );

add_action( 'wp_footer', function() {
	// make sure `core-block-supports-inline-css.css` is enqueued
	if ( !isset( wp_styles()->registered['core-block-supports'] ) ) {
		wp_register_style( 'core-block-supports', false );
		wp_add_inline_style( 'core-block-supports', "/* make sure core-block-supports-inline-css is enqueued */" );
		wp_enqueue_style( 'core-block-supports' );
	}
} );

// SPA libs
auto_include_files( UTLTS_JS_LIBS_SPA_DIRECTORY . '/pjax' );
auto_include_files( UTLTS_JS_LIBS_SPA_DIRECTORY . '/htmx' );