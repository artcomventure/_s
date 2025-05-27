<?php

define( 'CF7_DIRECTORY', dirname( __FILE__ ) );
define( 'CF7_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/inc/plugins/contact-form-7' );

// t9n
add_action( 'after_setup_theme', function() {
	load_theme_textdomain( 'cf7', CF7_DIRECTORY . '/languages' );
} );

// more indistinct way to hide the honeypots
add_filter( 'wpcf7_honeypot_container_css', function( $css ) {
	return 'text-indent: 100%; white-space: nowrap; overflow: hidden; position: absolute; clip: rect(1px, 1px, 1px, 1px); clip-path: inset(50%); z-index: -1;';
} );

add_filter( 'wpcf7_autop_or_not', '__return_false' );
add_filter( 'wpcf7_load_css', '__return_false' );

// auto include /inc files
auto_include_files( dirname( __FILE__ ) . '/inc' );
