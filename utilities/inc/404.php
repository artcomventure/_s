<?php

function get_404_page() {
	return apply_filters( 'wpml_object_id', get_option( 'error404' ), 'page' );
}

/**
 * Is this a 404 response or the configured 404 page (`error404`) requested directly?
 */
function is_404_page(): bool {
	return is_404() || (($page = get_404_page()) && is_page( $page ));
}

// register 404 page setting
add_filter( 'pages_for', function( $pages ) {
	$pages['error404'] = __( '404 not found error', 'utilities' );
	return $pages;
} );

/**
 * Set 404 responses and the configured 404 page (`error404`) to `noindex`.
 * The configured page needs it, as it's a regular page (HTTP 200) when requested directly.
 * @since 1.20.10
 */
// the WP way
add_filter( 'wp_robots', function( $robots ) {
	if ( is_404_page() ) {
		return wp_robots_no_robots( $robots );
	}

	return $robots;
} );

// the AIOSEO way
add_filter( 'aioseo_robots_meta', function( $attributes ) {
	if ( is_404_page() ) {
		$attributes['noindex'] = 'noindex';
	}

	return $attributes;
} );

// the old Yoast SEO way
add_filter( 'wpseo_robots', function( $robots ) {
	if ( is_404_page() ) {
		return 'noindex,follow';
	}

	return $robots;
} );

// the new Yoast SEO way
add_filter( 'wpseo_robots_array', function( $robots ) {
	if ( is_404_page() ) {
		$robots['index']   = 'noindex';
		$robots['follow']  = 'follow'; // oder 'nofollow', je nach Wunsch
	}

	return $robots;
}, 99 );

// removed @since 1.20.10 due to bad SEO/UX
//
// No 404 … redirect to home
//add_action( 'template_redirect', function () {
//	if ( is_404() ) {
//		if ( !get_404_page() ) {
//			wp_redirect( home_url() );
//			exit();
//		}
//	}
//} );
//
// setting description
//add_filter( 'page_for_error404_description', function( $description ) {
//	return __( 'Leave blank to redirect to front page.', 'utilities' );
//} );