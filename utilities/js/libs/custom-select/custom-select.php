<?php

define( 'CUSTOM_SELECT_DIRECTORY', dirname( __FILE__ ) );
define( 'CUSTOM_SELECT_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/js/libs/custom-select' );

// register custom-select lib
add_action('wp_enqueue_scripts', function() {
    wp_register_script('custom-select-module', CUSTOM_SELECT_DIRECTORY_URI . '/module/custom-select.min.js', [], '1.1.15', true);
    wp_register_script('custom-select', CUSTOM_SELECT_DIRECTORY_URI . '/app.js', ['custom-select-module'], false, true);

    $selectors = get_option('custom-select-css-selectors') ?: '';
    wp_localize_script('custom-select', 'wp_localize_script_vars', [
        'custom_selectors' => $selectors,
    ]);

    if (get_option('custom-select')) {
        wp_enqueue_script('custom-select');
    }
});

// enable/disable custom-select customizer setting
add_action( 'customize_register', function( $wp_customize ) {
    $wp_customize->add_setting( 'custom-select', array(
        'type' => 'option',
        'capability' => 'manage_options',
    ) );

    $wp_customize->add_control( 'custom-select', array(
        'label' => __( 'Enable custom-select', 'utilities' ),
        'description' => __( "Replacing and enhancing the native HTML select element.", 'utilities' ),
        'section' => 'utilities',
        'type' => 'checkbox'
    ) );

    $wp_customize->add_setting( 'custom-select-css-selectors', array(
        'type' => 'option',
        'capability' => 'manage_options',
    ) );

    $wp_customize->add_control( 'custom-select-css-selectors', array(
        'label'       => __( 'CSS Selectors for Custom Select Field', 'utilities' ),
        'description' => __( "Specify the CSS selectors of the select fields you want to customize. Leave this field empty to apply the custom styling to all select fields.", 'utilities' ),
        'section'     => 'utilities',
        'type'        => 'textarea'
    ) );

}, 9 );
