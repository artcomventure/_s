<?php

// preload web fonts to improve loading speed
// https://developer.mozilla.org/en-US/docs/Web/HTML/Preloading_content
add_filter( 'style_loader_tag', function( $tag, $handle, $src ) {
    if ( strpos( $src, 'font.css' ) !== false ) {
        $tag = preg_replace( '/rel=["|\']stylesheet["|\']/', "rel=\"stylesheet preload\" as=\"style\"", $tag );
    }

    return $tag;
}, 10, 3 );