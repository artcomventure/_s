<?php

/**
 * Register post type taxonomy for lists.
 */
add_action( 'init', function() {
	// `['TAXONOMY_NAME']`
	foreach ( apply_filters( 'register_list_taxonomy', [] ) as $post_type )
		register_taxonomy( "{$post_type}_list", $post_type, [
			'labels' => [
				'name' => _x( 'Lists', 'taxonomy general name', 'posts-list' ),
				'singular_name' => _x( 'List', 'taxonomy singular name', 'posts-list' ),
				'search_items' => __( 'Search Lists', 'posts-list' ),
				'all_items' => __( 'All Lists', 'posts-list' ),
				'edit_item' => __( 'Edit List', 'posts-list' ),
				'update_item' => __( 'Update List', 'posts-list' ),
				'add_new_item' => __( 'Add New List', 'posts-list' ),
				'new_item_name' => __( 'New List Name', 'posts-list' ),
			],
			'show_in_rest' => true,
			'hierarchical' => true,
			'publicly_queryable' => null,
			'rewrite' => ['slug' => "$post_type/list"],
		] );
} );

// suppress event filters for event lists.
add_filter( 'suppress_event_filter', function( $suppress, $query ) {
	if ( $query->is_tax( 'event_list' ) ) return true;
	return $suppress;
}, 10, 2 );

/**
 * Get ordered event posts of certain list.
 *
 * @param int|WP_Term $list
 * @param int $limit
 *
 * @return array
 */
function get_list_posts( int|WP_Term $list, int $limit = -1 ): array {
	if ( is_numeric( $list ) ) $list = get_term( $list );
	if ( !$list ) return [];

	// get IDs of all events of list
	return (new WP_Query( [
		'post_type' => preg_replace( '/_list$/', '', $list->taxonomy ),
		'posts_per_page' => $limit,
		'tax_query' => [[
			'taxonomy' => $list->taxonomy,
			'terms' => $list->term_id
		]]
	] ))->posts;
}

// auto include files
auto_include_files( POSTS_LIST_DIRECTORY . '/inc/taxonomy/inc' );