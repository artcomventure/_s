<?php

define( 'ACCORDION_DIRECTORY', dirname( __FILE__ ) );
define( 'ACCORDION_DIRECTORY_URI', GUTENBERG_DIRECTORY_URI . '/inc/accordion' );

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style('accordion-style', ACCORDION_DIRECTORY_URI . '/css/style.css', array(), filemtime(ACCORDION_DIRECTORY . '/css/style.css') );
	wp_enqueue_script('accordion-app', ACCORDION_DIRECTORY_URI . '/js/app.js', array(), filemtime(ACCORDION_DIRECTORY . '/js/app.js'), true );
} );

/**
 * Register block and enqueue styles and scripts.
 */
add_action( 'init', 'register_accordion' );
function register_accordion() {
	// automatically load dependencies and version
	$asset_file = include(ACCORDION_DIRECTORY . '/build/block.asset.php');

	wp_register_script(
		'accordion-be-js',
		ACCORDION_DIRECTORY_URI . '/build/block.js',
		$asset_file['dependencies'],
		$asset_file['version']
	);

	wp_set_script_translations( 'accordion-be-js', 'accordion', ACCORDION_DIRECTORY . '/languages' );

    wp_register_style(
        'accordion-be-css',
        ACCORDION_DIRECTORY_URI . '/css/gutenberg.css',
        array(),
        filemtime(ACCORDION_DIRECTORY . '/css/gutenberg.css')
    );

	register_block_type( 'accordion/block', array(
		'editor_script' => 'accordion-be-js',
        'editor_style' => 'accordion-be-css',
	) );
}

// allow post-teaser block by default
add_filter( 'allowed_block_types_all', 'allow_accordion_block', 11, 2 );
function allow_accordion_block( $allowed_blocks ) {
	if ( is_array( $allowed_blocks ) ) {
		$allowed_blocks[] = 'accordion/widget';
		$allowed_blocks[] = 'accordion/item';
		$allowed_blocks[] = 'accordion/content';
	}
	return $allowed_blocks;
}

// change i18n json file name to `LOCALE-HASH.json` like created by `wp i18n make-json`
add_filter( 'load_script_translation_file', function( $file, $handle, $domain ) {
	if ( $domain == 'accordion' ) {
		$md5 = md5('build/block.js');
		$file = preg_replace( '/\/(' . $domain . '-[^-]+)-.+\.json$/', "/$1-{$md5}.json", $file );
	}

	return $file;
}, 10, 3 );
