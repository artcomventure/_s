<?php

define( 'FEATURED_IMAGES_DIRECTORY', dirname( __FILE__ ) );
define( 'FEATURED_IMAGES_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/inc/featured-images' );

//add_action( 'customize_register', function( $wp_customize ) {
//	$wp_customize->add_setting( 'multiple-featured-images', array(
//		'type' => 'option',
//		'capability' => 'manage_options',
//	) );
//
//	$wp_customize->add_control( 'multiple-featured-images', array(
//		'label' => __( 'Multiple featured images', 'featured-images' ),
//		'section' => 'utilities',
//		'type' => 'checkbox'
//	) );
//} );
//
//if ( !get_option('multiple-featured-images' ) ) return;

/**
 * Register block and enqueue styles and scripts.
 */
add_action( 'init', function() {
	// automatically load dependencies and version
	$asset_file = include(FEATURED_IMAGES_DIRECTORY . '/build/block.asset.php');

	wp_register_script(
		'featured-images-be-js',
		FEATURED_IMAGES_DIRECTORY_URI . '/build/block.js',
		$asset_file['dependencies'],
		$asset_file['version']
	);

	wp_set_script_translations( 'featured-images-be-js', 'featured-images', FEATURED_IMAGES_DIRECTORY . '/languages' );

	register_block_type( 'featured-images/block', array(
		'editor_script' => 'featured-images-be-js'
	) );

	// all available post types
	foreach ( array_intersect_key(
		get_post_types( array( 'public' => TRUE ), 'objects' ),
		array_flip( get_post_types_by_support( array( 'custom-fields' ) ) )
	) as $post_type ) register_post_meta(
		$post_type->name,
		'_thumbnails',
		array(
			'show_in_rest' => [
				'schema' => [
					'type'  => 'array',
					'items' => [
						'type' => 'integer'
					]
				]
			],
			'type' => 'array',
			'single' => true,
			'auth_callback' => function () {
				return current_user_can('edit_posts' );
			}
		)
	);
}, 11 );

// change i18n json file name to `LOCALE-HASH.json` like created by `wp i18n make-json`
add_filter( 'load_script_translation_file', function( $file, $handle, $domain ) {
	if ( $domain == 'featured-images' && $handle == 'featured-images-be-js' ) {
		$md5 = md5('build/block.js');
		$file = preg_replace( '/\/(' . $domain . '-[^-]+)-.+\.json$/', "/$1-{$md5}.json", $file );
	}

	return $file;
}, 10, 3 );

// remove empty meta
add_action( 'added_post_meta', 'delete_empty_thumbnails_meta', 10, 4 );
add_action( 'updated_post_meta', 'delete_empty_thumbnails_meta', 10, 4 );
function delete_empty_thumbnails_meta( $meta_id, $post_id, $meta_key, $meta_value ) {
	if ( '_thumbnails' == $meta_key && !$meta_value )
		delete_post_meta( $post_id, '_thumbnails' );
}

/**
 * Retrieve an array of attachment IDs.
 *
 * @param int|WP_POST $post
 *
 * @return array
 */
function get_featured_images( int|WP_POST $post = 0 ): array {
	$post = get_post( $post );

	// get thumbnails (at least WP' thumbnail)
	$images = array_merge(
		[get_post_thumbnail_id()],
		(get_post_meta( $post->ID, '_thumbnails', true ) ?: [])
	);
	$images = array_filter( $images ); // remove empty values
	$images = array_unique( $images ); // unify values
	return array_values( $images ); // reset keys
}
