<?php

// register utilities panel with sections in customizer (empty sections are hidden)
// `utilities` is the general section, so controls of existing projects (`'section' => 'utilities'`) stay visible
add_action( 'customize_register', function( $wp_customize ) {
	$wp_customize->add_panel( 'utilities-panel', [
		'title' => __( 'Theme Options', 'utilities' ),
		'priority' => -1
	] );

	$sections = apply_filters( 'utilities_customizer_sections', [
		'utilities' => __( 'General', 'utilities' ),
		'utilities-ui' => __( 'UI/UX', 'utilities' ),
		'utilities-editor' => __( 'Editor & Content', 'utilities' ),
		'utilities-security' => __( 'Security', 'utilities' ),
	] );

	$priority = 0;
	foreach ( $sections as $id => $title ) {
		$wp_customize->add_section( $id, [
			'title' => $title,
			'panel' => 'utilities-panel',
			'priority' => $priority += 10,
		] );
	}
} );

// available 'stuff' @see ROOT/wp-includes/class-wp-customize-manager.php:register_controls()
add_action( 'customize_register', function( $wp_customize ) {
	$wp_customize->remove_section( 'custom_css' );
	$wp_customize->remove_section( 'header_image' );
	$wp_customize->remove_section( 'background_image' );
	$wp_customize->remove_section( 'colors' );

//	if ( !get_bloginfo( 'description', 'display' ) )
//		$wp_customize->remove_control( 'blogdescription' );

	if ( !user_has_role( 'administrator' ) )
		$wp_customize->remove_section( 'static_front_page' );
} );

/**
 * Remove "Customizer" from admin menu.
 *
 * @since 1.20.0
 */
add_action( 'wp_before_admin_bar_render', function() {
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu( 'customize' );
} );