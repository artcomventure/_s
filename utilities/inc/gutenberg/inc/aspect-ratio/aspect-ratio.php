<?php

define( 'ASPECTRATIO_DIRECTORY', dirname( __FILE__ ) );
define( 'ASPECTRATIO_DIRECTORY_URI', GUTENBERG_DIRECTORY_URI . '/inc/aspect-ratio' );

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_script('aspect-ratio', ASPECTRATIO_DIRECTORY_URI . '/app.js', [], false, true );
} );

/**
 * Enqueue gutenberg block styles and scripts.
 */
add_action( 'init', function() {
//	global $pagenow;
//	// TODO: not compatible with widgets editor :/
//	if ( $pagenow == 'widgets.php' ) return;

	// automatically load dependencies and version
	$asset_file = include( ASPECTRATIO_DIRECTORY . '/build/index.asset.php' );

	wp_register_script(
		'aspect-ratio-be-js',
		ASPECTRATIO_DIRECTORY_URI . '/build/index.js',
		$asset_file['dependencies'],
		$asset_file['version']
	);

	// t9n
	wp_set_script_translations( 'aspect-ratio-be-js', 'aspect-ratio', ASPECTRATIO_DIRECTORY . '/languages' );

	wp_register_style(
		'aspect-ratio-be-css',
		ASPECTRATIO_DIRECTORY_URI . '/gutenberg.css'
	);

	register_block_type( 'utilities/aspect-ratio', array(
		'editor_script' => 'aspect-ratio-be-js',
		'editor_style' => 'aspect-ratio-be-css',
	) );
} );

// change i18n json file name to `LOCALE-HASH.json` like created by `wp i18n make-json`
add_filter( 'load_script_translation_file', function( $file, $handle, $domain ) {
	if ( $domain == 'aspect-ratio' && $handle == 'aspect-ratio-be-js' ) {
		$md5 = md5('build/index.js');
		$file = preg_replace( '/\/(' . $domain . '-[^-]+)-.+\.json$/', "/$1-{$md5}.json", $file );
	}

	return $file;
}, 10, 3 );
