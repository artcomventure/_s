<?php

// disable WP search
add_action( 'customize_register', function( $wp_customize ) {
	$wp_customize->add_setting( 'disable_search', array(
		'type' => 'option',
		'capability' => 'manage_options',
		'sanitize_callback' => 'rest_sanitize_boolean',
	) );

	$wp_customize->add_control( 'disable_search', array(
		'label' => __( 'Disable Search', 'utilities' ),
		'description' => __( 'All search requests will show the 404 page.', 'utilities' ),
		'section' => 'utilities',
		'type' => 'checkbox'
	) );
} );

// No search … 404
add_action( 'template_redirect', function () {
	if ( get_option( 'disable_search' ) && is_search() ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
} );
