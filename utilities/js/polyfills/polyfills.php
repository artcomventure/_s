<?php

define( 'UTILITIES_JS_POLYFILLS_DIRECTORY', dirname( __FILE__ ) );
const UTILITIES_JS_POLYFILLS_DIRECTORY_URI = UTILITIES_DIRECTORY_URI . '/js/polyfills';

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_script( 'scroll-timeline', UTILITIES_JS_POLYFILLS_DIRECTORY_URI . '/scroll-timeline.js', [], '1.1.0' );
} );