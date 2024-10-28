<?php

define( 'POSTS_LIST_DIRECTORY', dirname( __FILE__ ) );
define( 'POSTS_LIST_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/inc/posts-list' );

// t9n
add_action( 'after_setup_theme', function() {
	load_theme_textdomain( 'posts-list', POSTS_LIST_DIRECTORY . '/languages' );
} );

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_script( 'posts-list', POSTS_LIST_DIRECTORY_URI . '/app.js', ['behaviours'], false, true );
} );

/**
 * Js endpoint for `get_posts_list()`.
 */
//add_action( 'rest_api_init', function() {
//	register_rest_route( 'posts-list/v1', '/query', array(
//		'methods'  => WP_REST_Server::READABLE,
//		'callback' => 'get_posts_list',
//		'permission_callback' => '__return_true',
//	) );
//} );

// auto include files
auto_include_files( POSTS_LIST_DIRECTORY . '/inc' );
