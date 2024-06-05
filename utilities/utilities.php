<?php

define( 'UTILITIES_DIRECTORY', dirname( __FILE__ ) );
define( 'UTILITIES_DIRECTORY_URI', get_template_directory_uri() . '/utilities' );

// t9n
add_action( 'after_setup_theme', function() {
	load_theme_textdomain( 'utilities', UTILITIES_DIRECTORY . '/languages' );
} );

// enable shortcodes for titles
add_filter( 'the_title', 'do_shortcode' );

add_action( 'wp_enqueue_scripts', function() {
	wp_register_script( 'behaviours', UTILITIES_DIRECTORY_URI . '/js/behaviours.js', ['alter'], '2.0.0', true );
	wp_register_script( 'alter', UTILITIES_DIRECTORY_URI . '/js/alter.js', [], '1.0.0', true );
	wp_register_script( 'cache', UTILITIES_DIRECTORY_URI . '/js/cache.js', [], '1.3.1', true );
	wp_register_script( 'bouncer', UTILITIES_DIRECTORY_URI . '/js/libs/bouncer.min.js', [], '1.4.6', true );

	wp_enqueue_script( 'helpers', UTILITIES_DIRECTORY_URI . '/js/helpers.js', [], false, true );
	wp_enqueue_script( 'external-links', UTILITIES_DIRECTORY_URI . '/js/external-links.js', ['behaviours'], false, true );
	wp_enqueue_script( 'inline-svg', UTILITIES_DIRECTORY_URI . '/js/inline-svg.js', ['behaviours'], false, true );
	wp_enqueue_script( 'custom-width', UTILITIES_DIRECTORY_URI . '/js/custom-width.js', ['behaviours'], false, true );

	wp_enqueue_style( 'admin-bar-ux', UTILITIES_DIRECTORY_URI . '/css/admin-bar.css' );
	wp_enqueue_script( 'css-breakpoints', UTILITIES_DIRECTORY_URI . '/js/BREAKPOINTS.js', [], false, true );
} );

add_action( 'admin_enqueue_scripts', function() {
	wp_enqueue_style( 'utilities-admin', UTILITIES_DIRECTORY_URI . '/css/admin.css' );
} );

// auto include utilities files
require_once UTILITIES_DIRECTORY . '/auto-include-files.php';
auto_include_files( UTILITIES_DIRECTORY . '/inc' );
auto_include_files( UTILITIES_DIRECTORY . '/js/libs' );
auto_include_files( UTILITIES_DIRECTORY . '/inc/plugins' );
auto_include_files( UTILITIES_DIRECTORY . '/inc/shortcodes' );
// auto include theme's /inc files
// TODO: theme vs child theme
auto_include_files( get_template_directory() . '/inc' );
