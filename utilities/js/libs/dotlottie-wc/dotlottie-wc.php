<?php

define( 'DOTLOTTIE_DIRECTORY', dirname( __FILE__ ) );
define( 'DOTLOTTIE_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/js/libs/dotlottie-wc' );

add_action( 'wp_enqueue_scripts', 'register_dotlottie_web_component' );
add_action( 'admin_enqueue_scripts', 'register_dotlottie_web_component' );
function register_dotlottie_web_component(): void {
	wp_register_script( 'dotlottie-wc', DOTLOTTIE_DIRECTORY_URI . '/build/app.js', ['behaviours'], false, true );
}

/**
 * Allow .lottie files.
 */
add_filter( 'upload_mimes', function( array $mime_types ):array {
	$mime_types['lottie'] = 'application/zip';
	return $mime_types;
} );

// auto include /inc files
auto_include_files( DOTLOTTIE_DIRECTORY . '/inc' );