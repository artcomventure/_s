<?php

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_script( 'dev-url', DEV_DIRECTORY_URI . '/inc/url/app.js', ['wp-i18n'], false, true );
    wp_set_script_translations( 'dev-url', 'dev', DEV_DIRECTORY . '/languages/' );
} );
