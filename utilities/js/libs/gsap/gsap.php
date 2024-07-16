<?php

define( 'GSAP_VERSION', '3.12.5' );

/**
 * Register gsap and it's plugins.
 * To enqueue plugins add "gsap-{PLUGIN_NAME}" to _your_ dependencies.
 */
add_action( 'wp_enqueue_scripts', function() {
	wp_register_script( 'gsap', LIBS_DIRECTORY_URI . '/gsap/gsap.min.js', array(), GSAP_VERSION, true );
	
	$plugins = [
		'CSSRulePlugin',
		'CustomEase',
		'Draggable',
		'EaselPlugin',
		'EasePack',
		'Flip',
		'MotionPathPlugin',
		'Observer',
		'PixiPlugin',
		'ScrollToPlugin',
		'ScrollTrigger',
		'TextPlugin'
	];

	foreach ( $plugins as $plugin ) {
		wp_register_script( "gsap-$plugin", LIBS_DIRECTORY_URI . "/gsap/$plugin.min.js", array( 'gsap' ), GSAP_VERSION, true );
	}

	wp_register_script( 'gsap-all', false, array_map( function( $plugin ) {
		return "gsap-$plugin";
	}, $plugins ), GSAP_VERSION, true );
} );
