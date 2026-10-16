<?php

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_script( 'dev-url', UTLTS_DEV_DIRECTORY_URI . '/inc/url/app.js', ['wp-i18n'], false, ['in_footer' => true, 'strategy' => 'defer'] );
    wp_set_script_translations( 'dev-url', 'dev', UTLTS_DEV_DIRECTORY . '/languages/' );
} );
