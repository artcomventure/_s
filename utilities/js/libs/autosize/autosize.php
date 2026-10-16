<?php

define( 'UTLTS_JS_LIBS_AUTOSIZE_DIRECTORY', dirname( __FILE__ ) );
define( 'UTLTS_JS_LIBS_AUTOSIZE_DIRECTORY_URI', UTLTS_DIRECTORY_URI . '/js/libs/autosize' );

// register pjax lib
add_action( 'wp_enqueue_scripts', function() {
	wp_register_script( 'autosize-module', UTLTS_JS_LIBS_AUTOSIZE_DIRECTORY_URI . '/module/autosize.min.js', [], '6.0.1', ['in_footer' => true, 'strategy' => 'defer'] );
	wp_register_script( 'autosize', UTLTS_JS_LIBS_AUTOSIZE_DIRECTORY_URI . '/app.js', ['autosize-module'], false, ['in_footer' => true, 'strategy' => 'defer'] );
} );
