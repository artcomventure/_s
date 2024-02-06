<?php

/**
 * Since custom plugins could have the same naming as plugins listed on https://wordpress.org/plugins/
 * we remove any update notification caused by these _name overlaps_.
 *
 * In most cases the plugins remove their notification by themself
 * ... but that only takes effect once the plugin is activated.
 */

add_filter( 'site_transient_update_plugins', function( $value ) {
    foreach ( array(
        'slider/slider.php',
    ) as $plugin_file ) {
        if ( isset( $value->response[$plugin_file] ) ) {
            unset( $value->response[$plugin_file] );
        }
    }

    return $value;
} );