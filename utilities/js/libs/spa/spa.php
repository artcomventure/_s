<?php

/**
 * JavaScript libraries for a single page application (SPA).
 */

define( 'UTLTS_JS_LIBS_SPA_DIRECTORY', dirname( __FILE__ ) );
const UTLTS_JS_LIBS_SPA_DIRECTORY_URI = UTLTS_DIRECTORY_URI . '/js/libs/spa';

// with pjax all core block styles are needed (switched pages don't load their own styles),
// without pjax only styles of blocks used on the page (default for classic themes is `false`)
add_filter( 'should_load_separate_core_block_assets', fn() => !get_option( 'pjax' ) );
//add_filter( 'should_load_block_assets_on_demand', '__return_false' );

add_action( 'wp_footer', function() {
	// make sure `core-block-supports-inline-css.css` is enqueued (pjax switches it)
	if ( get_option( 'pjax' ) && !isset( wp_styles()->registered['core-block-supports'] ) ) {
		wp_register_style( 'core-block-supports', false );
		wp_add_inline_style( 'core-block-supports', "/* make sure core-block-supports-inline-css is enqueued */" );
		wp_enqueue_style( 'core-block-supports' );
	}
} );

// SPA libs
auto_include_files( UTLTS_JS_LIBS_SPA_DIRECTORY . '/pjax' );
auto_include_files( UTLTS_JS_LIBS_SPA_DIRECTORY . '/htmx' );