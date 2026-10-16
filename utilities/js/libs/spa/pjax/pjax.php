<?php

define( 'UTLTS_JS_LIBS_SPA_PJAX_DIRECTORY', dirname( __FILE__ ) );
const UTLTS_JS_LIBS_SPA_PJAX_DIRECTORY_URI = UTLTS_DIRECTORY_URI . '/js/libs/spa/pjax';

// register pjax lib
add_action( 'wp_enqueue_scripts', function() {
	wp_register_script( 'pjax-module', UTLTS_JS_LIBS_SPA_PJAX_DIRECTORY_URI . '/module/pjax.min.js', [], '0.2.8', ['in_footer' => true, 'strategy' => 'defer'] );
	wp_register_script( 'pjax', UTLTS_JS_LIBS_SPA_PJAX_DIRECTORY_URI . '/app.js', ['pjax-module', 'behaviours', 'alter'], false, ['in_footer' => true, 'strategy' => 'defer'] );

	if ( get_option( 'pjax' ) )
		wp_enqueue_script( 'pjax' );

	// add custom pjax style/script in case pjax is enqueued
	if ( wp_script_is('pjax' ) ) {
		// DON'T load async because pjax script requires CSS values
		wp_enqueue_style( 'pjax', UTLTS_JS_LIBS_SPA_PJAX_DIRECTORY_URI . '/style.css' );
		if ( $hex = get_background_color() ) {
			wp_add_inline_style( 'pjax', "body.custom-background #pjax-transition { background-color: #{$hex}; }" );
		}
	}
}, 9 );

// enable/disable pjax customizer setting
add_action( 'customize_register', function( $wp_customize ) {
	$wp_customize->add_setting( 'pjax', array(
		'type' => 'option',
		'capability' => 'manage_options',
	) );

	$wp_customize->add_control( 'pjax', array(
		'label' => __( 'Enable Pjax', 'utilities' ),
		'description' => __( "Load page content using AJAX without reloading your page's layout.", 'utilities' ),
		'section' => 'utilities',
		'type' => 'checkbox'
	) );
}, 9 );
