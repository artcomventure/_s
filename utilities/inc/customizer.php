<?php

// register utilities section in customizer
add_action( 'customize_register', function( $wp_customize ) {
	$wp_customize->add_section( 'utilities', [
		'title' => __( 'Theme Options', 'utilities' ),
		'priority' => -1
	] );
} );

// available 'stuff' @see ROOT/wp-includes/class-wp-customize-manager.php:register_controls()
add_action( 'customize_register', function( $wp_customize ) {
	$wp_customize->remove_section( 'custom_css' );
	$wp_customize->remove_section( 'header_image' );
	$wp_customize->remove_section( 'background_image' );

//	if ( !get_bloginfo( 'description', 'display' ) )
//		$wp_customize->remove_control( 'blogdescription' );

	if ( !user_has_role( 'administrator' ) )
		$wp_customize->remove_section( 'static_front_page' );
} );