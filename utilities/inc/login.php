<?php

/**
 * Show project's logo on login screen.
 *
 * via `login_head` action @since 1.20.12
 */
add_action( 'login_head', function() {
	if ( ! $logo_id = get_theme_mod( 'custom_logo' ) ) return;
	if ( ! $logo = wp_get_attachment_image_src( $logo_id, 'full' ) ) return;

	[ $url, $width, $height ] = $logo;

	$ratio = ( $width && $height ) ? 'aspect-ratio: ' . (int) $width . ' / ' . (int) $height . '; height: auto;' : '';

	echo '<style>.login h1 a { background-image: url(' . esc_url( $url ) . "); background-size: contain; width: auto; max-width: 100%; $ratio }</style>";
} );

add_filter( 'login_headerurl', function() { return home_url(); } );
add_filter( 'login_headertext', function() { return get_bloginfo( 'name' ); } );