<?php

define( 'HIDE_DIRECTORY', dirname( __FILE__ ) );
define( 'HIDE_DIRECTORY_URI', GUTENBERG_DIRECTORY_URI . '/inc/hide' );

/**
 * Enqueue gutenberg block styles and scripts.
 */
add_action( 'init', function() {
	// automatically load dependencies and version
	$asset_file = include( HIDE_DIRECTORY . '/build/index.asset.php' );

	wp_register_script(
		'hide-be-js',
		HIDE_DIRECTORY_URI . '/build/index.js',
		$asset_file['dependencies'],
		$asset_file['version']
	);

	wp_set_script_translations( 'hide-be-js', 'hide', HIDE_DIRECTORY . '/languages' );

	wp_register_style(
		'hide-be-css',
		HIDE_DIRECTORY_URI . '/css/gutenberg.css'
	);

	register_block_type( 'utilities/hide', array(
		'editor_script' => 'hide-be-js',
		'editor_style' => 'hide-be-css',
	) );
} );

// change i18n json file name to `LOCALE-HASH.json` like created by `wp i18n make-json`
add_filter( 'load_script_translation_file', function( $file, $handle, $domain ) {
	if ( $domain == 'hide' && $handle == 'hide-be-js' ) {
		$md5 = md5('build/index.js');
		$file = preg_replace( '/\/' . $domain . '-([^-]+)-.+\.json$/', "/$domain-$1-$md5.json", $file );
	}

	return $file;
}, 10, 3 );
