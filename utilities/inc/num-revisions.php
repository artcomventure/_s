<?php /**
 * To prevent database size from bloating and potential performance issues,
 * we limit revisions to 20 per post.
 *
 * @since 1.20.0
 */

add_filter( 'wp_revisions_to_keep', function( $num, $post ) {
	return 20;
}, 10, 2 );
