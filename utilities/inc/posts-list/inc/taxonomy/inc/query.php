<?php

/**
 * This will sort all `POST_TYPE_list` queries.
 *
 * To sort them by manually given order we can't use `post__in` attribute
 * since the order list might not be up-to-date with current pool of posts in list.
 *
 * So we query all events and will sort (and slice) result after query is done (see `the_posts` filter).
 */
add_action( 'pre_get_posts', function( $query ) {
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
 * Order queried posts.
 */
add_filter( 'the_posts', function( $posts, $query ) {
	foreach ( apply_filters( 'register_list_taxonomy', [] ) as $post_type ) {
		if ( $query->is_tax( "{$post_type}_list" ) ) {
			// get list order
			if ( $order = get_option( 'list-posts-order', [] )["{$post_type}_list"][$query->queried_object_id] ?? [] ) {
				$post_ids = array_column( $posts, 'ID' );

				// make sure all ordered ids are still in taxonomy term
				$order = array_intersect( $order, $post_ids );
				// make sure ordered list contains all posts of taxonomy term (append new ones)
				$order = array_unique( array_merge( $order, $post_ids ) );

				// sort posts by `$post_order`
				$posts = array_map( function( $event_id ) use ( $posts, $post_ids ) {
					return $posts[array_search( $event_id, $post_ids )];
				}, $order );

				// not quite sure if this is necessary
				// _but for the sake of the god of cleanliness_
				if ( $ppp = $query->get( '_posts_per_page' ) ) {
					$posts = array_slice( $posts, 0, $ppp );
					$query->set( 'posts_per_page', $ppp );
					$query->get( '_posts_per_page', false );
				}
			}
		}
	}

	return $posts;
}, 10, 2 );
