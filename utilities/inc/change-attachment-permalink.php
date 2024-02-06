<?php

/**
 * Change attachment's `post_name` to current title (sanitized) for permalink.
 */
add_filter( 'wp_insert_attachment_data', function( $data, $postarr ) {
	$data['post_name'] = wp_unique_post_slug(
		sanitize_title( $postarr['post_title'] ),
		$postarr['ID'],
		$postarr['post_status'],
		$postarr['post_type'],
		$postarr['post_parent']
	);

	return $data;
}, 10, 2 );

/**
 * Change attachment's permalink structure.
 */
add_action( 'generate_rewrite_rules', function( $wp_rewrite ) {
	$new_rules = array();
	$new_rules['attachment/(\d*)$'] = 'index.php?attachment_id=$matches[1]';
	$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
} );

/**
 * Change attachment links to new structure.
 */
add_filter( 'attachment_link', function( $link, $post_id ) {
	$post = get_post( $post_id );
	return home_url( '/attachment/' . $post->post_name );
}, 20, 2 );
