<?php

define( 'GUTENBERG_DIRECTORY', dirname( __FILE__ ) );
define( 'GUTENBERG_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/inc/gutenberg' );

add_action( 'after_setup_theme', function() {
	add_theme_support( 'editor-styles' );
	add_theme_support( 'align-wide' );
	// more settings see `THEME/theme.json`
	// https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-json/

	// relative path from `functions.php`
	add_editor_style(  './css/editor-style.css' );

	// load post type specific css
	if ( $cssdir = opendir( get_template_directory() . '/css' ) ) {
		while ( ($file = readdir( $cssdir )) !== false ) {
			// check for `editor-POST_TYPE.css`
			if ( !preg_match( '/^editor-(.+)\.css$/', $file, $match ) ) continue;

			if ( get_current_post_type() == 'event' ) {
				add_editor_style(  "./css/$file" );
			}
		}

		closedir( $cssdir );
	}
} );

// add `?ver=FILEMTIME` to editor-style.css
add_filter( 'tiny_mce_before_init', function( $mce_init ) {
	$mce_init['cache_suffix'] = 'ver=' . filemtime(get_template_directory() . '/css/editor-style.css');
	return $mce_init;
} );

// remove _unneeded_ CSS
add_action( 'wp_enqueue_scripts', function() {
	wp_dequeue_style( 'global-styles' );
	// for some reason _sometimes_ this doesn't work with `global-styles-inline-css
	// it's removed in `THEME/utilities/js/helpers.js`
} );

// remove all core patterns
add_action( 'after_setup_theme', function() {
	remove_theme_support('core-block-patterns' );
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
