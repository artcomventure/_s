<?php

define( 'UTLTS_JS_LIBS_DOTLOTTIEWC_DIRECTORY', dirname( __FILE__ ) );
define( 'UTLTS_JS_LIBS_DOTLOTTIEWC_DIRECTORY_URI', UTLTS_DIRECTORY_URI . '/js/libs/dotlottie-wc' );

add_action( 'wp_enqueue_scripts', 'register_dotlottie_web_component' );
add_action( 'admin_enqueue_scripts', 'register_dotlottie_web_component' );
function register_dotlottie_web_component(): void {
	wp_register_script( 'dotlottie-wc', UTLTS_JS_LIBS_DOTLOTTIEWC_DIRECTORY_URI . '/build/app.js', ['behaviours'], false, ['in_footer' => true, 'strategy' => 'defer'] );

	// WASM file location, independent of the theme's folder name (see `setWasmUrl()` in `app.js`)
	wp_add_inline_script( 'dotlottie-wc', 'window.dotlottieWasmUrl = ' . wp_json_encode( UTLTS_JS_LIBS_DOTLOTTIEWC_DIRECTORY_URI . '/dotlottie-player.wasm' ) . ';', 'before' );
}

/**
 * Allow .lottie files.
 */
add_filter( 'upload_mimes', function( array $mime_types ):array {
	$mime_types['lottie'] = 'application/zip';
	return $mime_types;
} );

// auto include /inc files
auto_include_files( UTLTS_JS_LIBS_DOTLOTTIEWC_DIRECTORY . '/inc' );