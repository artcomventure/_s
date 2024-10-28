<?php

/**
 * Enqueue gutenberg block styles and scripts.
 */
add_action( 'init', function() {
	$post_types = array_intersect_key(
		get_post_types( array( 'public' => TRUE ), 'objects' ),
		array_flip( get_post_types_by_support( array( 'custom-fields' ) ) )
	);

	// enable for all public post type with custom fields support
	if ( !wp_is_json_request() && !in_array( get_current_post_type(), array_keys( $post_types ) ) ) return;

	foreach ( $post_types as $post_type )
		foreach ( ['_hidetitle' => 'boolean', '_subtitle' => 'string'] as $meta_key => $type ) {
			register_post_meta(
				$post_type->name,
				$meta_key,
				array(
					'show_in_rest'  => true,
					'single' => true,
					'type' => $type,
					'auth_callback' => function () {
						return current_user_can('edit_posts' );
					}
				)
			);
	}

	// automatically load dependencies and version
	$asset_file = include( TITLE_DIRECTORY . '/build/panel.asset.php' );

	wp_register_script(
		'title-panel-js',
		TITLE_DIRECTORY_URI . '/build/panel.js',
		$asset_file['dependencies'],
		$asset_file['version']
	);

	wp_set_script_translations( 'title-panel-js', 'title', TITLE_DIRECTORY . '/languages' );

	register_block_type( 'title/panel', array(
		'editor_script' => 'title-panel-js',
	) );
}, 11 );

// change i18n json file name to `LOCALE-HASH.json` like created by `wp i18n make-json`
add_filter( 'load_script_translation_file', function( $file, $handle, $domain ) {
	if ( $domain == 'title' && $handle == 'title-panel-js' ) {
		$md5 = md5('build/panel.js');
		$file = preg_replace( '/\/(' . $domain . '-[^-]+)-.+\.json$/', "/$1-$md5.json", $file );
	}

	return $file;
}, 10, 3 );
