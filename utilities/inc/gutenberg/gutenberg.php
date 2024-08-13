<?php

define( 'GUTENBERG_DIRECTORY', dirname( __FILE__ ) );
define( 'GUTENBERG_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/inc/gutenberg' );

add_action( 'after_setup_theme', function() {
	add_theme_support( 'editor-styles' );
	// relative path from `functions.php`
	add_editor_style(  './css/editor-style.css' );
}, 11 );

// add `?ver=FILEMTIME` to editor-style.css
add_filter( 'tiny_mce_before_init', function( $mce_init ) {
	$mce_init['cache_suffix'] = 'ver=' . filemtime(get_template_directory() . '/css/editor-style.css');
	return $mce_init;
} );

add_action( 'after_setup_theme', function() {
    add_theme_support( 'align-wide' );

	// more settings see `THEME/theme.json`
	// https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-json/
} );

// block editor
add_action( 'init', function() {
	// automatically load dependencies and version
	$asset_file = include( GUTENBERG_DIRECTORY . '/build/index.asset.php' );

	wp_register_script(
		'gutenberg-be-js',
		GUTENBERG_DIRECTORY_URI . '/build/index.js',
		$asset_file['dependencies'],
		$asset_file['version']
	);

	// enqueue
	register_block_type( 'gutenberg/stuff', array(
		'editor_script' => 'gutenberg-be-js',
		array(
			'attributes' => array(
				'background-color' => array(
					'type' => 'string',
					'default' => get_background_color(),
				)
			)
		)
	) );
} );

add_action( 'rest_api_init', function() {
	register_rest_route('gutenberg/v1', '/getBackgroundColor', [
		'method' => 'GET',
		'permission_callback' => function () {
			return current_user_can('edit_posts' );
		},
		'callback' => function() {
			return rest_ensure_response( get_background_color() );
		}
	] );
} );

// auto include /inc files
auto_include_files( dirname( __FILE__ ) . '/inc' );
