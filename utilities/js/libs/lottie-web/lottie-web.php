<?php

add_action( 'wp_enqueue_scripts', function() {
	wp_register_script( 'lottie-web', UTLTS_JS_LIBS_DIRECTORY_URI . '/lottie-web/lottie.min.js', [], '5.12.2', ['in_footer' => true, 'strategy' => 'defer'] );
} );