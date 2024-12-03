<?php

define( 'POST_EDIT_LINK_DIRECTORY', dirname( __FILE__ ) );
define( 'POST_EDIT_LINK_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/inc/post-edit-link' );

add_action( 'wp_enqueue_scripts', function() {
	if ( !is_user_logged_in() ) return;
	wp_enqueue_script( 'utilities--post-edit-link', POST_EDIT_LINK_DIRECTORY_URI . '/app.js', ['alter'], false, true );
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
	// within this function (in `get_edit_post_link()`) it's checked if the current user can edit posts
	// this can't be checked on ajax requests :/
	// therefor the `$edit_link` will be empty
	edit_post_link( '<span>' . get_post_type_object( get_post_type( $post ) )->labels->edit_item . '</span>', '', '', $post->ID );
	$edit_link = ob_get_clean();
	// switch `a` to `span[data-href]`
	$edit_link = preg_replace( '/^<a /', '<span data-href="' . get_edit_post_link( $post->ID ) . '" tabindex="-1" ', $edit_link );
	$edit_link = preg_replace( '/<\/a>$/', '</span>', $edit_link );

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
	if ( strpos( $title, 'post-edit-link' ) === false )
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

// Gutenberg styles
add_action( 'after_setup_theme', function() {
	add_theme_support( 'editor-styles' );
	// relative path from `functions.php`
	add_editor_style(  './utilities/inc/post-edit-link/style.css' );
}, 11 );
