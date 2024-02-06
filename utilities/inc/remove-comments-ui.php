<?php

/**
 * To keep everything as clean as possible:
 * This file removes all comment stuff from WP's backend.
 */

// remove '+ New Comment' link from admin bar
add_action( 'admin_bar_menu', function() {
	if ( get_option( 'default_comment_status' ) != 'open' && !get_comment_count()['all'] ) {
        global $wp_admin_bar;
        $wp_admin_bar->remove_node( 'comments' );
    }
}, 999 );

// remove admin menu entry
add_action( 'admin_menu', function() {
	if ( get_option( 'default_comment_status' ) != 'open' ) {
		if ( !get_comment_count()['all'] ) remove_menu_page( 'edit-comments.php' );

		// ...and meta boxes for ALL posts types
		foreach ( get_post_types() as $post_type ) {
			remove_meta_box( 'commentstatusdiv', $post_type, 'normal' );
			remove_meta_box( 'commentsdiv', $post_type, 'normal' );
		}
	}
}, 999 );

// redirect to dashboard
add_action( 'admin_init', function() {
	global $pagenow;

	if ( $pagenow == 'edit-comments.php' && get_option( 'default_comment_status' ) != 'open' && !get_comment_count()['all'] ) {
		wp_redirect( admin_url(), 301 );
		exit;
	}
} );

// remove post column
foreach ( get_post_types() as $post_type ) {
    add_filter( "manage_{$post_type}_posts_columns", function( $columns ) {
        if ( get_option( 'default_comment_status' ) != 'open' && !get_comment_count()['all'] ) {
            unset( $columns['comments'] );
        }

        return $columns;
    } );
}
