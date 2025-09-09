<?php

// duplicate nav menus for each (additional) language
add_action( 'after_setup_theme', 'bogo_localize_menus' );
function bogo_localize_menus() {
    global $_wp_registered_nav_menus;
    $wplang = get_option( 'WPLANG' );

    $localizable_menus = array();

    foreach ( $_wp_registered_nav_menus as $key => $label ) {
        foreach ( bogo_available_languages() as $locale => $lang ) {
            $description = $label . ' - ' . $lang;

            // default (WP) stays default
            if ( $locale == $wplang ) {
                $localizable_menus[$key] = $description;
            }
            // add localizable nav menu
            else $localizable_menus[$key . '__' . $locale] = $description;
        }
    }

    register_nav_menus( $localizable_menus );
}

// load localized menu
add_filter( 'wp_nav_menu_args', 'bogo_localized_menu' );
function bogo_localized_menu( $args ) {
    $locale = get_locale();

    if ( !bogo_is_default_locale( $locale ) ) {
        $args['theme_location'] .= '__' . $locale;
    }

    return $args;
}