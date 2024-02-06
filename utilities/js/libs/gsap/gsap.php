<?php

add_action( 'wp_enqueue_scripts', function() {
	wp_register_script( 'gsap', LIBS_DIRECTORY_URI . '/gsap/gsap.min.js', array(), '3.11.4', true );
	wp_register_script( 'gsap-ScrollTrigger', LIBS_DIRECTORY_URI . '/gsap/ScrollTrigger.min.js', array( 'gsap' ), '3.11.4', true );
	wp_register_script( 'gsap-Draggable', LIBS_DIRECTORY_URI . '/gsap/Draggable.min.js', array( 'gsap' ), '3.11.4', true );
} );
