<?php

/**
 * Wrapper for adding WP notices.
 */

/**
 * @param string $message
 * @param string $type
 * @param boolean $inline
 * @param boolean|null $dismissible
 */
function notice( string $message = '', string $type = 'info', bool $inline = false, $dismissible = null ) {
    echo get_notice( $message, $type, $inline, $dismissible );
}

/**
 * @param string $message
 * @param string $type
 * @param boolean $inline
 * @param boolean|null $dismissible
 *
 * @return string
 */
function get_notice( string $message = '', string $type = 'info', bool $inline = false, $dismissible = null ) {
    $dismissible = is_null($dismissible) ? $inline : $dismissible;
    return sprintf( '<div class="notice notice-%1$s' . ($inline ? ' inline' : '') . ($dismissible ? ' is-dismissible' : '') . '"><p>%2$s</p></div>', esc_attr( $type ), $message );
}

/**
 * @param string $message
 * @param string $type
 * @param bool $dismissible
 */
function inline_notice( string $message = '', string $type = 'info', bool $dismissible = false ) {
    echo get_inline_notice( $message, $type, $dismissible );
}

/**
 * @param string $message
 * @param string $type
 * @param bool $dismissible
 *
 * @return string
 */
function get_inline_notice( string $message = '', string $type = 'info', bool $dismissible = false ) {
    return get_notice( $message, $type, true, $dismissible );
}