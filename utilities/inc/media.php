<?php

/**
 * Allow additional image mime types.
 */

add_filter( 'upload_mimes', function( $mimes ){
	$mimes['svg'] = 'image/svg+xml';
	$mimes['vcf'] = 'text/vcard';

	return $mimes;
} );

// https://kulturbanause.de/blog/svg-dateien-in-die-wordpress-mediathek-hochladen/#chapter3
add_filter( 'wp_check_filetype_and_ext', function( $checked, $file, $filename, $mimes ) {
	if( !$checked['type'] ) {
		$wp_filetype = wp_check_filetype( $filename, $mimes );
		$ext = $wp_filetype['ext'];
		$type = $wp_filetype['type'];
		$proper_filename = $filename;

		if($type && 0 === strpos( $type, 'image/' ) && $ext !== 'svg' ) {
			$ext = $type = false;
		}

		$checked = compact('ext','type','proper_filename' );
	}

	return $checked;
}, 10, 4 );

/**
 * Add a default image alt (attachment title).
 */
add_filter( 'wp_get_attachment_image_attributes', function( $attr, $attachment ) {
	// don't override existing alt text
	if ( empty( $attr['alt'] ) ) {
		$attr['alt'] = get_the_title( $attachment );
	}

	return $attr;
}, 10, 2 );

/**
 * Force `figcaption` to be added.
 */

add_action( 'customize_register', function( $wp_customize ) {
	$wp_customize->add_setting( 'force_figcaption', array(
		'type' => 'option',
		'capability' => 'manage_options',
	) );

	$wp_customize->add_control( 'force_figcaption', array(
		'label' => __( 'Empty caption', 'utilities' ),
		'description' => __( "Force <code>&lt;figcaption&gt;</code> to be added, even if it's empty. Mostly for styling reasons.", 'utilities' ),
		'section' => 'utilities',
		'type' => 'checkbox'
	) );
} );

// add media's caption to media-text block
add_filter( 'render_block', function( $block_content, $block ) {
	if ( $block['blockName'] === 'core/media-text' ) {
		if ( ($mediaId = $block['attrs']['mediaId'] ?? null) ) {
			$caption = wp_get_attachment_caption( $mediaId );
		}

		if ( ($caption = $caption ?? '') || get_option( 'force_figcaption' ) )
			$block_content = str_replace('</figure>', '<figcaption>' . $caption . '</figcaption></figure>', $block_content );
	}
	else if ( $block['blockName'] === 'core/image' ) {
		if ( strpos( $block_content, '<figcaption' ) === false && get_option( 'force_figcaption' ) )
			$block_content = str_replace('</figure>', '<figcaption></figcaption></figure>', $block_content );
	}

	return $block_content;
}, 10, 2 );

/**
 * Add image size dimension to (BE) name.
 */
add_filter( 'image_size_names_choose', function( $size_names ) {
	$subsizes = wp_get_registered_image_subsizes();

	foreach ( $size_names as $name => $label ) {
		if ( !isset($subsizes[$name]) ) continue;

		$width = $subsizes[$name]['width'] ?: __( 'auto', 'gans' );
		$height = $subsizes[$name]['height'] ?: __( 'auto', 'gans' );

		$size_names[$name] .= " ({$width} x {$height})";
	}

	// order by size
	uksort( $size_names, function( $a, $b ) use ( $subsizes ) {
		if ( $a == 'full' ) return 1;
		if ( $b == 'full' ) return -1;

		if ( $subsizes[$a]['width'] == $subsizes[$b]['width'] ) {
			if ( $subsizes[$a]['height'] == $subsizes[$b]['height'] ) return 1;
		}
		else {
			if ( $subsizes[$a]['width'] > $subsizes[$b]['width'] ) return 1;
		}

		return -1;
	} );

	return $size_names;
}, 0 );

/**
 * Crop WP's changeable image sizes if width and height are given.
 */
add_action( 'admin_init', 'maybe_crop_media', 100 );
add_action( 'init', 'maybe_crop_media', 100 );
function maybe_crop_media() {
	foreach ( wp_get_registered_image_subsizes() as $name => $settings ) {
		if ( !in_array( $name, ['medium', 'large'] ) ) continue;
		if ( !$settings['width'] || !$settings['height'] ) continue;

		add_image_size( $name, $settings['width'], $settings['height'], true );
	}
}