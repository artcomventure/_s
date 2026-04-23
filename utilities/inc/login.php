<?php

add_action( 'login_enqueue_scripts', function() {
	$theme = wp_get_theme();

	wp_enqueue_style( "{$theme->stylesheet}-login", get_template_directory_uri() . '/css/login.css' );
	if ( $logo = get_theme_mod( 'custom_logo' ) ) {
		if ( $logo = wp_get_attachment_image_src( $logo, 'full' ) ) {
			wp_add_inline_style( "{$theme->stylesheet}-login", ".login h1 a {
				background-image: url(" . $logo[0] . ");
				background-size: contain;
				width: auto;
				max-width: 100%;
				" . ( ($logo[1] && $logo[2]) ? "aspect-ratio: " . $logo[1] . "/" . $logo[2] . ";" : '' ) . ";
				" . ( ($logo[1] && $logo[2]) ? "height: auto;" : '' ) . ";
			}" );
		}
	}
} );

home_url();

add_filter( 'login_headerurl', function() { return home_url(); } );
add_filter( 'login_headertext', function() { return get_bloginfo( 'name' ); } );