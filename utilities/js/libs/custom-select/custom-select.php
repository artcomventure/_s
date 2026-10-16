<?php

define( 'UTLTS_JS_LIBS_CUSTOMSELECT_DIRECTORY', dirname( __FILE__ ) );
define( 'UTLTS_JS_LIBS_CUSTOMSELECT_DIRECTORY_URI', UTLTS_DIRECTORY_URI . '/js/libs/custom-select' );

// register custom-select lib
add_action( 'wp_enqueue_scripts', function() {
    wp_register_script('custom-select-module', UTLTS_JS_LIBS_CUSTOMSELECT_DIRECTORY_URI . '/module/custom-select.min.js', [], '1.1.15', ['in_footer' => true, 'strategy' => 'defer'] );
    wp_register_script('custom-select', UTLTS_JS_LIBS_CUSTOMSELECT_DIRECTORY_URI . '/app.js', ['custom-select-module'], false, ['in_footer' => true, 'strategy' => 'defer'] );

    wp_localize_script('custom-select', 'customSelectVars', [
        'selectors' => trim( get_option( 'custom-select-css-selectors', '' ) ),
    ] );

	if ( get_option( 'custom-select' ) ) {
		wp_enqueue_script('custom-select' );
	}

	// auto enqueue default styles
	if ( wp_script_is('custom-select-module' ) ) {
		wp_enqueue_style('custom-select-module', UTLTS_JS_LIBS_CUSTOMSELECT_DIRECTORY_URI . '/module/custom-select.css', [], '1.1.15', 'async' );

		wp_add_inline_style( 'custom-select-module', '.custom-select-opener span:empty:before {
			content: "' . __( 'Select' ) . '";
		}' );
	}
}, 9 );

add_action( 'customize_controls_enqueue_scripts', function() {
	wp_enqueue_script( 'custom-select-be', UTLTS_JS_LIBS_CUSTOMSELECT_DIRECTORY_URI . '/admin.js', [], false, true );
} );

// enable/disable custom-select customizer setting
add_action( 'customize_register', function( $wp_customize ) {
    $wp_customize->add_setting( 'custom-select', array(
        'type' => 'option',
        'capability' => 'manage_options',
    ) );

    $wp_customize->add_control( 'custom-select', array(
        'label' => __( 'Enable custom-select.js', 'utilities' ),
        'description' => __( "Replacing and enhancing the native <code>select</code> (dropdown) element.", 'utilities' ),
        'section' => 'utilities',
        'type' => 'checkbox'
    ) );

    $wp_customize->add_setting( 'custom-select-css-selectors', array(
        'type' => 'option',
        'capability' => 'manage_options',
    ) );

    $wp_customize->add_control( 'custom-select-css-selectors', array(
//        'label'       => __( 'CSS Selectors', 'utilities' ),
        'description' => __( "CSS selectors for <code>select</code> fields to apply custom-select.js to. Leave blank to apply to all. Each selector in a new line.", 'utilities' ),
        'section'     => 'utilities',
        'type'        => 'textarea'
    ) );

} );
