<?php

function get_404_page() {
	return apply_filters( 'wpml_object_id', get_option( 'error-404' ), 'page' );
}

// No 404 … redirect to home
add_action( 'template_redirect', function () {
    if ( is_404() && !get_404_page() ) {
        wp_redirect( home_url() );
        exit();
    }
} );

// register 404 page setting
add_filter( 'pages_for', function( $pages ) {
	$pages['error-404'] = __( '404 not found error', 'utilities' );
	return $pages;
} );

// setting description
add_filter( 'page_for_error-404_description', function( $description ) {
	return __( 'Leave blank to redirect to front page.', 'utilities' );
} );

// TODO: in case a 404 page is selected automatically change template and query to this page
//add_filter( '404_template_hierarchy', function( $templates ) {
//	if ( $postid = get_404_page() ) {
//		array_unshift( $templates, 'page.php' );
//
//		global $wp_query;
//		$wp_query = new WP_Query( [
//			'post_type' => 'page',
//			'p' => $postid
//		] );
//	}
//
//	return $templates;
//} );