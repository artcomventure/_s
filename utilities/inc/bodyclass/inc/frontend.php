<?php

add_filter( 'body_class', function ( $classes ) {
    // Only pages
    if ( !is_page() )
        return $classes;

    $bodyclass = get_post_meta(get_the_ID(), '_bodyclass', 1);

    if ( !$bodyclass )
        return $classes;

    array_push($classes, $bodyclass);

    return $classes;
} );