<?php

// disable responsive image sizes
add_filter( 'intermediate_image_sizes_advanced', function( $sizes ) {
	unset( $sizes['medium_large'] );
	unset( $sizes['1536x1536'] );
	unset( $sizes['2048x2048'] );

	return $sizes;
} );

// auto create responsive images (for scrset)
//add_action( 'admin_init', 'maybe_crop_media', 100 );
//add_action( 'init', 'maybe_crop_media', 100 );
//function maybe_crop_media() {
//	foreach ( wp_get_registered_image_subsizes() as $name => $settings ) {
//		// WP's changeable default sizes only
//		if ( !in_array( $name, ['medium', 'large'] ) ) continue;
//		if ( !$settings['width'] || !$settings['height'] ) continue;
//
//		add_image_size( $name, $settings['width'], $settings['height'], true );
//	}
//}

// disable srcset on frontend
add_filter( 'max_srcset_image_width', function() {
	return 1;
} );
