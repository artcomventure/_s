<?php

/**
 * Try to replace css and js files with minimized versions.
 *
 * Minimized version must be in same directory and must pass the following naming convention:
 * - not minimized file name: FILENAME.css
 * - minimized file name: FILENAME.min.css
 */

add_action( 'wp_enqueue_scripts', function() {
    $home_path = ( function_exists( 'get_home_path' ) ? get_home_path() : $_SERVER['DOCUMENT_ROOT'] );
    $home_url  = preg_quote( get_home_url(), '/' );

    // got for all css and js files
    foreach ( array( wp_styles(), wp_scripts() ) as $files ) {
        foreach ( $files->registered as $file ) {
            // not minimized yet
            if ( ! is_string( $file->src ) || ! preg_match( '/(?<!\.min)\.(css|js)$/', $file->src ) ) {
                continue;
            }
            // is no local file
            if ( ! preg_match( '/^(\/|' . $home_url . ')/', $file->src ) ) {
                continue;
            }

            // relative (from DOCUMENT_ROOT) path to minimized version
            $minimized = preg_replace( array(
                '/^' . $home_url . '/',
                '/\.(css|js)$/'
            ), array( '', ".min.$1" ), $file->src );

            // check if minimized file exists
            if ( file_exists( $home_path . $minimized ) ) {
                // ... and replace/insert it
                $file->src = $minimized;
            }
        }
    }
}, 1982 );
