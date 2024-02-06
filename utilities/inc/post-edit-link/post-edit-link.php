<?php

define( 'POST_EDIT_LINK_DIRECTORY', dirname( __FILE__ ) );
define( 'POST_EDIT_LINK_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/inc/post-edit-link' );

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'utilities--post-edit-link', POST_EDIT_LINK_DIRECTORY_URI . '/style.css', ['dashicons'] );
} );

/**
 * Append post edit link to post title.
 */

// automatically append edit link to teaser posts
// single post's edit link is available in admin bar

add_filter( 'the_title', function( $title, $post_id ) {
	global $more;

    if ( get_the_ID() == $post_id && !$more && !(is_admin() || wp_is_json_request() || wp_doing_ajax()) && $title ) {
        $title .= get_post_edit_link( $post_id );
    }

    return $title;
}, 10, 2 );

/**
 * Retrieve post edit link.
 *
 * @param int|WP_Post $post
 *
 * @return string
 */
function get_post_edit_link( $post = 0 ) {
	$post = get_post( $post );

	ob_start();
    edit_post_link( '<span>' . get_post_type_object( get_post_type( $post ) )->labels->edit_item . '</span>', '', '', $post->ID );
    $edit_link = ob_get_contents();
    ob_end_clean();

    return $edit_link ?: '';
}

// custom `[get]_the_post_title()` functions to force edit link
// more or less duplicates from WP's `[get_]the_title()` @see wp-includes/post-template.php

/**
 * Retrieve post title.
 *
 * @param int|WP_Post $post Optional. Post ID or WP_Post object. Default is global $post.
 *
 * @return string
 */
function get_the_post_title( $post = 0 ) {
    $title = get_the_title( $post );
    $title .= get_post_edit_link( $post );

    return $title;
}

/**
 * Display or retrieve the current post title with optional markup.
 *
 * @param string $before
 * @param string $after
 * @param bool $echo
 *
 * @return string|void
 */
function the_post_title( $before = '', $after = '', $echo = true ) {
    $title = get_the_post_title();

    if ( strlen( $title ) == 0 ) {
        return;
    }

    $title = $before . $title . $after;

    if ( $echo ) {
        echo $title;
    } else {
        return $title;
    }
}
