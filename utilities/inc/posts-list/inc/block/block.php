<?php

/**
 * Enqueue gutenberg block styles and scripts.
 */
add_action( 'init', function() {
	// automatically load dependencies and version
	$asset_file = include( POSTS_LIST_DIRECTORY . '/inc/block/build/index.asset.php' );

	wp_register_script(
		'posts-list-block-js',
		POSTS_LIST_DIRECTORY_URI . '/inc/block/build/index.js',
		$asset_file['dependencies'],
		$asset_file['version']
	);

	wp_register_style(
		'posts-list-block-css',
		POSTS_LIST_DIRECTORY_URI . '/inc/block/style.css'
	);

	wp_set_script_translations( 'posts-list-block-js', 'posts-list', POSTS_LIST_DIRECTORY . '/languages' );

	// make _same_ settings in `./index.js`
	$blockAttributes = [
		'blockId' => [
			'type' => 'string',
		],
		'post_type' => [
			'type' => 'string',
			'default' => 'post'
		],
		'filter' => [
			'type' => 'string',
			'default' => '[]'
		],
		'posts_per_page' => [
			'type' => 'string',
			'default' => get_option( 'post_per_page' )
		],
		'theme' => [
			'type' => 'string',
			'default' => 'grid'
		],
		'group_by_month' => [
			'type' => 'boolean',
			'default' => false
		],
		'columns' => [
			'type' => 'string',
			'default' => '1'
		],
		'orderby' => [
			'type' => 'string',
			'default' => 'date'
		],
		'more' => [
			'type' => 'string',
			'default' => ''
		],
	];

	register_block_type( 'posts-list/block', array(
		'editor_script' => 'posts-list-block-js',
		'editor_style' => 'posts-list-block-css',
		'attributes' => $blockAttributes
	) );

	// used for serverside render
	register_block_type( 'posts-list/preview', array(
		'render_callback' => 'get_posts_list',
		'attributes' => $blockAttributes
	) );
} );

// change i18n json file name to `LOCALE-HASH.json` like created by `wp i18n make-json`
add_filter( 'load_script_translation_file', function( $file, $handle, $domain ) {
	if ( $domain == 'posts-list' && $handle == 'posts-list-block-js' ) {
		$md5 = md5('inc/block/build/index.js');
		$file = preg_replace( '/\/(' . $domain . '-[^-]+)-.+\.json$/', "/$1-{$md5}.json", $file );
	}

	return $file;
}, 10, 3 );
