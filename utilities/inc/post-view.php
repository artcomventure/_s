<?php

// add post's _view status_
add_filter( 'post_class', function ( $classes ) {
	global $more;
	$classes[] = $more ? 'full-view' : 'teaser-view';

	if ( get_the_ID() == get_option( 'page_on_front' ) )
		$classes[] = 'home';

	return $classes;
} );

// change _page for posts_' canonical url to itself
add_filter( 'get_canonical_url', function( $canonical_url, $post ) {
	if ( is_home() && $page_for_posts = get_option( 'page_for_posts' ) )
		$canonical_url = get_permalink( $page_for_posts );
	return $canonical_url;
}, 10, 2 );

