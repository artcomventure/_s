<?php

/**
 * Converts hex color code to rgb array.
 *
 * @param string $hex
 * @return array|false
 */
function hex2Rgb( string $hex ): array|false {
    $hex = str_replace( "#", "", $hex );

    if ( strlen( $hex ) == 3 ) {
        $r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
        $g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
        $b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
    } else if ( strlen( $hex ) == 6 ) {
        $r = hexdec( substr( $hex, 0, 2 ) );
        $g = hexdec( substr( $hex, 2, 2 ) );
        $b = hexdec( substr( $hex, 4, 2 ) );
    }
    else return false;

    return [ $r, $g, $b ];
}

/**
 * @param int|string $r
 * @param int|string $g
 * @param int|string $b
 * @return float
 */
function lightness(int|string $r, int|string $g = '', int|string $b = '' ): float {
    // we assume $r is a hex value
    if ( !$g && !$b ) {
        list( $r, $g, $b ) = hex2Rgb( $r );
    }

    return (max($r, $g, $b) + min($r, $g, $b)) / 510.0; // HSL algorithm
}
