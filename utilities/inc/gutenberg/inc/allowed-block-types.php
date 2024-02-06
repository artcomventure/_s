<?php

add_filter( 'allowed_block_types_all', function( $allowed_blocks, $post ) {
//	$blocks = array(
//		'core/quote',
//		'core/columns',
//		'core/buttons',
//		'core/gallery',
//		'core/group',
//		'core/heading',
//		'core/html',
//		'core/image',
//		'core/media-text',
//		'core/paragraph',
//		'core/shortcode',
//		'core/spacer',
//		'core/post-title',
//		'core/list',
////		'core/latest-posts',
////		'core/embed',
////		'core/video',
//		'ub/content-toggle-block',
//		'ub/content-toggle'
//	);

    if ( is_array( $allowed_blocks ) && count($blocks ?? []) )
	    $allowed_blocks = array_unique( array_merge( $allowed_blocks, $blocks ) );

    return $allowed_blocks;
}, 10, 2 );
