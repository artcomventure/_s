<?php

// display or retrieve the current post subtitle with optional markup
function the_subtitle( $before = '', $after = '', $echo = true ) {
	$subtitle = get_the_subtitle();

	if ( strlen( $subtitle ) == 0 ) {
		return '';
	}

	$subtitle = $before . $subtitle . $after;

	if ( $echo ) echo $subtitle;
	else return $subtitle;
}

// retrieve post subtitle
function get_the_subtitle( $post = 0 ) {
	$post = get_post( $post );

	$id = $post->ID ?? 0;
	$subtitle = get_post_meta( $id, '_subtitle', true );

	return apply_filters( 'the_subtitle', $subtitle, $id );
}

// remove empty subtitle
add_action( "added_post_meta", 'remove_empty_subtitle', 10, 4 );
add_action( "updated_post_meta", 'remove_empty_subtitle', 10, 4 );
function remove_empty_subtitle( $meta_id, $object_id, $meta_key, $_meta_value ) {
	if ( !$_meta_value && $meta_key == '_subtitle' ) {
		delete_post_meta( $object_id, $meta_key );
	}
}
