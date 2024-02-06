<?php

/**
 * Wrapper for editing custom _page for_ pages.
 */

// readings _pages for_ reading settings
add_action( 'admin_init', function() {
	foreach( apply_filters( 'pages_for', array() ) as $name => $label ) {
		register_setting( 'reading', $name );
	}
} );

// add _pages for_ settings to WP's reading options setting page
add_action( 'admin_init', function() {
    add_settings_section(
        'pages_for_settings',
        '',
        function() {},
        'reading'
    );

    add_settings_field(
        'page_for',
        __( 'Page for …', 'utilities' ),
        function() {
            echo '<ul style="margin: 0;">';
            foreach( apply_filters( 'pages_for', array() ) as $name => $label ) {
	            echo '<li>';
	            printf(
		            "<label>{$label}: %s</label>",
		            wp_dropdown_pages(
			            array(
				            'name' => $name,
				            'echo' => 0,
				            'show_option_none' => __( '&mdash; Select &mdash;' ),
				            'option_none_value' => '0',
				            'selected' => get_option( $name ),
			            )
		            )
	            );
	            echo '</li>';
            }
            echo '</ul>';

            if ( $description = apply_filters( "page_for_{$name}_description", '', $name, $label ) ) {
            	echo '<p class="description">' . $description . '</p>';
            }
        },
        'reading',
        'pages_for_settings'
    );
} );

// add customizer _pages for_ section and controls
add_action( 'customize_register', function( $wp_customize ) {
	$wp_customize->add_section( 'pages_for', array(
		'title' => __( 'Pages for …', 'utilities' ),
		'priority' => 100
	)  );

	foreach( apply_filters( 'pages_for', array() ) as $name => $label ) {
		$wp_customize->add_setting( $name, array(
			'type' => 'option',
			'capability' => 'manage_options',
		) );

		$wp_customize->add_control( $name, array(
			'label' => $label,
			'section' => 'pages_for',
			'type' => 'dropdown-pages',
			'allow_addition' => true,
		) );
	}
} );

// mark selected page for downloads in page grid
add_filter( 'display_post_states', function( $post_states, $post ) {
	foreach( apply_filters( 'pages_for', array() ) as $name => $label ) {
		if ( $post->ID == apply_filters( 'wpml_object_id', get_option( $name ), 'page' ) ) {
			$post_states[$name] = $label;
		}
	}

	return $post_states;
}, 10, 2 );

// add _page for_ specific class to ...
add_filter( 'body_class', 'page_for_class' );
add_filter( 'post_class', 'page_for_class' );
function page_for_class( $classes ) {
	if ( $post = get_post() ) foreach( apply_filters( 'pages_for', array() ) as $name => $label ) {
		if ( $post->ID == apply_filters( 'wpml_object_id', get_option( $name ), 'page' ) ) {
			$classes[] = sanitize_html_class( str_replace( '_', '-', $name ) );
		}
	}

	return $classes;
}