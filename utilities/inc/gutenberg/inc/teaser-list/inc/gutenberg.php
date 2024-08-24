<?php

/**
 * Enqueue gutenberg block styles and scripts.
 */
add_action( 'init', function() {
	// automatically load dependencies and version
	$asset_file = include(TEASERLIST_DIRECTORY . '/build/block.asset.php');

	wp_register_script(
		'teaser-list-block-js',
		TEASERLIST_DIRECTORY_URI . '/build/block.js',
		$asset_file['dependencies'],
		$asset_file['version']
	);

	wp_set_script_translations( 'teaser-list-block-js', 'teaser-list', TEASERLIST_DIRECTORY . '/languages' );

	register_block_type( 'teaser-list/block', array(
		'editor_script' => 'teaser-list-block-js',

		'attributes' => [
			'post_type' => [
				'type' => 'string',
				'default' => 'post'
			],
			'posts_per_page' => [
				'type' => 'string',
				'default' => get_option( 'post_per_page' )
			],
			'orderby' => [
				'type' => 'string',
				'default' => 'date'
			]
		]
	) );

	// used for serverside render
	register_block_type( 'teaser-list/preview', array(
		'render_callback' => 'get_teaser_list',

		'attributes' => [
			'post_type' => [
				'type' => 'string',
				'default' => 'post'
			],
			'posts_per_page' => [
				'type' => 'string',
				'default' => get_option( 'post_per_page' )
			],
			'orderby' => [
				'type' => 'string',
				'default' => 'date'
			],
			'columns' => [
				'type' => 'string',
				'default' => '1'
			],
			'more' => [
				'type' => 'string',
				'default' => ''
			],
		]
	) );
} );

// change i18n json file name to `LOCALE-HASH.json` like created by `wp i18n make-json`
add_filter( 'load_script_translation_file', function( $file, $handle, $domain ) {
	if ( $domain == 'teaser-list' && $handle == 'teaser-list-block-js' ) {
		$md5 = md5('build/block.js');
		$file = preg_replace( '/\/(' . $domain . '-[^-]+)-.+\.json$/', "/$1-{$md5}.json", $file );
	}

	return $file;
}, 10, 3 );
