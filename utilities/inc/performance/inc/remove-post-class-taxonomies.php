<?php

/**
 * I've never needed the taxonomy class names ...
 *
 * @since 1.20.8
 */
add_filter( 'post_class_taxonomies', function ( $taxonomies ) {
	return [];
} );
