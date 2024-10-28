<?php

define( 'BREAKPOINT_DIRECTORY', dirname( __FILE__ ) );
define( 'BREAKPOINT_DIRECTORY_URI', GUTENBERG_DIRECTORY_URI . '/inc/breakpoint' );

/**
 * Enqueue gutenberg block styles and scripts.
 */
add_action( 'init', function() {
	global $pagenow;
	// TODO: not compatible with widgets editor :/
	if ( $pagenow == 'widgets.php' ) return;

	// automatically load dependencies and version
	$asset_file = include( BREAKPOINT_DIRECTORY . '/build/index.asset.php' );

	wp_register_script(
		'breakpoint-be-js',
		BREAKPOINT_DIRECTORY_URI . '/build/index.js',
		$asset_file['dependencies'],
		$asset_file['version']
	);

	wp_set_script_translations( 'breakpoint-be-js', 'breakpoint', BREAKPOINT_DIRECTORY . '/languages' );

	wp_register_style(
		'breakpoint-be-css',
		BREAKPOINT_DIRECTORY_URI . '/css/gutenberg.css'
	);

	register_block_type( 'utilities/breakpoint', array(
		'editor_script' => 'breakpoint-be-js',
		'editor_style' => 'breakpoint-be-css',
	) );
} );

// add attributes to all block registries
add_filter( 'register_block_type_args', function( $args ) {
	$args['attributes'] += [
		'breakpoint' => [
			'type'    => 'string',
			'default' => '',
		]
	];

	return $args;
} );

// change i18n json file name to `LOCALE-HASH.json` like created by `wp i18n make-json`
add_filter( 'load_script_translation_file', function( $file, $handle, $domain ) {
	if ( $domain == 'breakpoint' && $handle == 'breakpoint-be-js' ) {
		$md5 = md5('build/index.js');
		$file = preg_replace( '/\/' . $domain . '-([^-]+)-.+\.json$/', "/$domain-$1-$md5.json", $file );
	}

	return $file;
}, 10, 3 );
