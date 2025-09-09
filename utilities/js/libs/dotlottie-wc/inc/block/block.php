<?php

/**
 * Register block and enqueue styles and scripts.
 */
add_action( 'init', function() {
	// automatically load dependencies and version
	$asset_file = include(DOTLOTTIE_DIRECTORY . '/inc/block/build/block.asset.php');
	$asset_file['dependencies'][] = 'dotlottie-wc';

	wp_register_script(
		'dotlottie-be-js',
		DOTLOTTIE_DIRECTORY_URI . '/inc/block/build/block.js',
		$asset_file['dependencies'],
		$asset_file['version']
	);

	wp_set_script_translations( 'dotlottie-be-js', 'dotlottie', DOTLOTTIE_DIRECTORY . '/languages' );

	register_block_type( 'dotlottie/wc', array(
		'editor_script' => 'dotlottie-be-js'
	) );
} );

// change i18n json file name to `LOCALE-HASH.json` like created by `wp i18n make-json`
add_filter( 'load_script_translation_file', function( $file, $handle, $domain ) {
	if ( $file && $domain == 'dotlottie' ) {
		if ( $handle == 'dotlottie-be-js' ) {
			$filename = explode( '/', $file );
			$filename = array_pop( $filename );

			$md5 = md5('inc/block/block.js' );
			$replacement = preg_replace( '/' . $domain . '-([^-]+)[^.]+\.json$/', "$1-{$md5}.json", $filename );

			$file = str_replace( $filename, $replacement, $file );
		}
	}

	return $file;
}, 10, 3 );
