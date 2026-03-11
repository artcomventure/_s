<?php

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( !is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) return;

define( 'CF7_DIRECTORY', dirname( __FILE__ ) );
define( 'CF7_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/inc/plugins/contact-form-7' );

// t9n
add_action( 'after_setup_theme', function() {
	load_theme_textdomain( 'cf7', CF7_DIRECTORY . '/languages' );
} );

// pjax ready @since 1.20.2
add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_script( 'utilities--contact-form-7', CF7_DIRECTORY_URI . '/app.js', ['behaviours'], false, true );
} );

add_filter( 'wpcf7_autop_or_not', '__return_false' );
add_filter( 'wpcf7_load_css', '__return_false' );

add_filter( 'wpcf7_form_elements', 'do_shortcode' );

// remove hashtag @since 1.20.2
//add_filter( 'wpcf7_form_action_url', function( $action_url ) {
//	return explode( '#', $action_url )[0];
//} );

// auto include /inc files
auto_include_files( dirname( __FILE__ ) . '/inc' );
