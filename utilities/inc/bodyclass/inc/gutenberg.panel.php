<?php

/**
 * Enqueue gutenberg block styles and scripts.
 */
add_action( 'init', function() {
	register_post_meta( 'page', "_bodyclass", array(
		'show_in_rest' => true,
		'single' => true,
		'type' => 'string',
		'auth_callback' => function () {
			return current_user_can('edit_posts' );
		}
	) );

	// automatically load dependencies and version
	$asset_file = include( BODYCLASS_DIRECTORY . '/build/panel.asset.php' );

	wp_register_script(
		'bodyclass-panel-js',
		BODYCLASS_DIRECTORY_URI . '/build/panel.js',
		$asset_file['dependencies'],
		$asset_file['version']
	);

	wp_set_script_translations( 'bodyclass-panel-js', 'bodyclass', BODYCLASS_DIRECTORY . '/languages' );

	register_block_type( 'bodyclass/panel', array(
		'editor_script' => 'bodyclass-panel-js',
	) );
} );

// change i18n json file name to `LOCALE-HASH.json` like created by `wp i18n make-json`
add_filter( 'load_script_translation_file', function( $file, $handle, $domain ) {
	if ( $domain == 'bodyclass' && $handle == 'bodyclass-panel-js' ) {
		$md5 = md5('build/panel.js');
		$file = preg_replace( '/\/' . $domain . '-([^-]+)-.+\.json$/', "/$1-{$md5}.json", $file );
	}

	return $file;
}, 10, 3 );
