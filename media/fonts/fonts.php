<?php

define( 'FONTS_DIRECTORY', dirname( __FILE__ ) );
define( 'FONTS_DIRECTORY_URI', get_template_directory_uri() . '/media/fonts' );

define( 'TYPEKIT_FONTS', apply_filters( 'typekit-font-url', '' ) );

/**
 * Enqueue fonts.
 */
add_action( 'wp_enqueue_scripts', function() {
	if ( TYPEKIT_FONTS ) wp_enqueue_style( 'typekit-fonts', TYPEKIT_FONTS );
	wp_enqueue_style( 'icont', FONTS_DIRECTORY_URI . '/Icont/font.css' );
} );

/**
 * Add fonts to gutenberg.
 */
add_action( 'after_setup_theme', function() {
	if ( TYPEKIT_FONTS ) add_editor_style(  TYPEKIT_FONTS );
	// relative path from `functions.php`
	add_editor_style(  './media/fonts/Icont/font.css' );
}, 11 );
