<?php

add_filter( 'header_class', function( $classes, $css_class, $post_id ) {
	global $more;

	if ( $more && get_post_meta( $post_id, '_hidetitle', true ) ) {
		$classes[] = 'screen-reader-text';
	}

	return $classes;
}, 10, 3 );
