<?php

// disable WP search
add_action( 'customize_register', function( $wp_customize ) {
	$wp_customize->add_setting( 'disable_search', array(
		'type' => 'option',
		'capability' => 'manage_options',
	) );

	$wp_customize->add_control( 'disable_search', array(
		'label' => __( 'Disable Search', 'utilities' ),
		'description' => __( 'All search requests will be redirected to home page.', 'utilities' ),
		'section' => 'utilities',
		'type' => 'checkbox'
	) );
} );

// No search … redirect to home
add_action( 'template_redirect', function () {
	if ( get_option('disable_search' ) && is_search() ) {
		wp_redirect( home_url() );
		exit();
	}
} );
