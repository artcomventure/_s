<?php

add_action( 'wp_enqueue_scripts', function() {
	wp_register_script( 'lottie-web', LIBS_DIRECTORY_URI . '/lottie-web/lottie.min.js', [], '5.12.2', true );
} );