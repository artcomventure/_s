<?php

/**
 * Disables canonical redirection for paginated posts/pages.
 * Fixes the issue of redirecting from `/SLUG/page/2/` to `/SLUG/`
 */

add_action( 'parse_query', function( $query ) {
	if ( ( !empty($query->query_vars['name']) || // post query
	       !empty($query->query_vars['pagename']) || // page query
	       // any post-type
	       in_array(
			   $query->query_vars['post_type'] ?? '',
			   array_keys( get_post_types( ['public' => TRUE] ) )
	       )
	     ) &&
	     true === $query->is_singular &&
	     -1 == $query->current_post &&
	     true === $query->is_paged
	) add_filter( 'redirect_canonical', '__return_false' );

	return $query;
} );
