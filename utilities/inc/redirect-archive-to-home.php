<?php

// no need for category archive in case there is only one (default) category
add_action( 'template_redirect', function () {
	if ( is_category() && count(get_categories()) == 1 ) {
		if ( $page_for_posts = get_option( 'page_for_posts' ) )
			$location = get_permalink( $page_for_posts );
		wp_redirect( $location ?? home_url(), 301 );
	}
} );