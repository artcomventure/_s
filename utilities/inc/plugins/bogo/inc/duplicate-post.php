<?php

// Yoast Duplicate Post : exclude `_original_post` from being duplicated
add_filter( 'duplicate_post_excludelist_filter', function( $meta_excludelist ) {
	$meta_excludelist[] = '_original_post';
	return $meta_excludelist;
} );
