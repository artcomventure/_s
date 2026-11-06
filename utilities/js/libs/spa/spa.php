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

// SPA libs
auto_include_files( UTLTS_JS_LIBS_SPA_DIRECTORY . '/pjax' );
auto_include_files( UTLTS_JS_LIBS_SPA_DIRECTORY . '/htmx' );