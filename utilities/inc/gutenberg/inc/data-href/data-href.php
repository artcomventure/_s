<?php

define( 'DATAHREF_DIRECTORY', dirname( __FILE__ ) );
define( 'DATAHREF_DIRECTORY_URI', GUTENBERG_DIRECTORY_URI . '/inc/data-href' );

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style('data-href-style', DATAHREF_DIRECTORY_URI . '/css/style.css' );
	wp_enqueue_script('data-href-app', DATAHREF_DIRECTORY_URI . '/app.js', [], false, true );
} );

/**
 * Enqueue gutenberg block styles and scripts.
 */
add_action( 'init', function() {
	// automatically load dependencies and version
	$asset_file = include( DATAHREF_DIRECTORY . '/build/attribute.asset.php' );

	wp_register_script(
		'data-href-attribute-be-js',
		DATAHREF_DIRECTORY_URI . '/build/attribute.js',
		$asset_file['dependencies'],
		$asset_file['version']
	);

	wp_set_script_translations( 'data-href-attribute-be-js', 'data-href', DATAHREF_DIRECTORY . '/languages' );

	wp_register_style(
		'data-href-attribute-be-css',
		DATAHREF_DIRECTORY_URI . '/css/gutenberg.attribute.css'
	);

	register_block_type( 'data-href/attribute', array(
		'editor_script' => 'data-href-attribute-be-js',
		'editor_style' => 'data-href-attribute-be-css',
	) );
} );

// change i18n json file name to `LOCALE-HASH.json` like created by `wp i18n make-json`
add_filter( 'load_script_translation_file', function( $file, $handle, $domain ) {
	if ( $domain == 'data-href' && $handle == 'data-href-attribute-be-js' ) {
		$md5 = md5('build/attribute.js');
		$file = preg_replace( '/\/' . $domain . '-([^-]+)-.+\.json$/', "/$1-{$md5}.json", $file );
	}

	return $file;
}, 10, 3 );
