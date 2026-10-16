<?php

/**
 * This will sort all `POST_TYPE_list` queries.
 *
 * To sort them by manually given order we can't use `post__in` attribute
 * since the order list might not be up-to-date with current pool of posts in list.
 *
 * So we query all posts and will sort (and slice) result after query is done (see `the_posts` filter).
 */
add_action( 'pre_get_posts', function( $query ) {
	if ( !is_list_order_query( $query ) ) return;
	// `fields => ids|id=>parent` skips `the_posts`: no sorting possible, keep native pagination
	if ( $query->get( 'fields' ) ) return;

	foreach ( apply_filters( 'register_list_taxonomy', [] ) as $post_type ) {
		if ( $query->is_tax( "{$post_type}_list" ) ) {
			// posts are only ordered after query is made, so ...
			if ( ($ppp = $query->get( 'posts_per_page' )) > -1 || !$ppp ) {
				// ... we store original `posts_per_page` value in query
				$query->set( '_posts_per_page', $ppp ?: get_option( 'posts_per_page' ) );
				// ... and set query to get all posts
				$query->set( 'posts_per_page', -1 );
				// we will change it back in `the_posts` filter
			}
		}
	}
} );

/**
 * Manual list order only applies if the query is ordered by `menu_order` (first).
 * e.g. `'menu_order'`, `'menu_order title'`, `['menu_order' => 'ASC', 'title' => 'ASC']`
 */
function is_list_order_query( WP_Query $query ): bool {
	$orderby = $query->get( 'orderby' );
	$first = is_array( $orderby ) ? array_key_first( $orderby ) : strtok( (string) $orderby, ' ,' );

	return strtolower( (string) $first ) === 'menu_order';
}

/**
 * Order queried posts.
 */
add_filter( 'the_posts', function( $posts, $query ) {
	if ( !is_list_order_query( $query ) ) return $posts;

	foreach ( apply_filters( 'register_list_taxonomy', [] ) as $post_type ) {
		if ( !$query->is_tax( "{$post_type}_list" ) ) continue;

		// get list order
		if ( $order = get_option( 'list-posts-order', [] )["{$post_type}_list"][$query->queried_object_id] ?? [] ) {
			// `fields => ids` returns ids instead of posts
			$post_ids = array_map( fn( $post ) => is_object( $post ) ? $post->ID : (int) $post, $posts );

			// make sure all ordered ids are still in taxonomy term
			$order = array_intersect( $order, $post_ids );
			// make sure ordered list contains all posts of taxonomy term (append new ones)
			$order = array_unique( array_merge( $order, $post_ids ) );

			// sort posts by `$post_order`
			$posts = array_map( function( $post_id ) use ( $posts, $post_ids ) {
				return $posts[array_search( $post_id, $post_ids )];
			}, $order );
		}

		// paginate: query got all posts (see `pre_get_posts`)
		if ( $ppp = $query->get( '_posts_per_page' ) ) {
			// `offset` overrides `paged` (like WP_Query)
			$offset = is_numeric( $query->get( 'offset' ) )
				? (int) $query->get( 'offset' )
				: ( max( 1, (int) $query->get( 'paged' ) ) - 1 ) * $ppp;

			$query->found_posts = count( $posts );
			$query->max_num_pages = (int) ceil( $query->found_posts / $ppp );

			$posts = array_values( array_slice( $posts, $offset, $ppp ) );

			// cleanup
			$query->set( 'posts_per_page', $ppp );
			$query->set( '_posts_per_page', false );
		}

		break;
	}

	return $posts;
}, 10, 2 );
