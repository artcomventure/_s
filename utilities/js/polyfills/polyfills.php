<?php

define( 'UTLTS_JS_POLYFILLS_DIRECTORY', dirname( __FILE__ ) );
const UTLTS_JS_POLYFILLS_DIRECTORY_URI = UTLTS_DIRECTORY_URI . '/js/polyfills';

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_script( 'scroll-timeline', UTLTS_JS_POLYFILLS_DIRECTORY_URI . '/scroll-timeline.js', [], '1.1.0' );
} );

/**
 * Load scroll timeline polyfill only if scroll-driven animations aren't supported natively.
 * Inserted by script, so it doesn't block rendering.
 */
add_filter( 'script_loader_tag', function( $tag, $handle, $src ) {
    if ( $handle !== 'scroll-timeline' ) return $tag;

    return wp_get_inline_script_tag( sprintf(
        "CSS.supports( 'animation-timeline: --works' ) || document.head.append( Object.assign( document.createElement( 'script' ), { src: %s } ) );",
        wp_json_encode( $src )
    ) );
}, 10, 3 );
