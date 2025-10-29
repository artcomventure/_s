<?php

define( 'ANCHOR_NAVIGATION_DIRECTORY', dirname( __FILE__ ) );
define( 'ANCHOR_NAVIGATION_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/inc/anchor-navigation' );

// t9n
add_action( 'after_setup_theme', function() {
	load_theme_textdomain( 'anchor-navigation', ANCHOR_NAVIGATION_DIRECTORY . '/languages' );
} );

/**
 * Register scripts and styles.
 */
add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_script( 'anchor-navigation', ANCHOR_NAVIGATION_DIRECTORY_URI . '/app.js', ['behaviours'], false, true );
} );

/**
 * Generate anchor navigation.
 *
 * @param string $post_content
 *
 * @return string
 */
function get_anchor_navigation( string $post_content ): string {
	if ( $anchors = parse_anchors( $post_content ) ) {
		$toggler = '<button class="anchors-toggle" aria-expanded="false">' . __( 'Jump to', 'anchor-navigation' ) . '</button>';

		$output = "<div class=\"anchors\">$toggler<nav>";

		do_action( 'prepend_anchor_navigation', $anchors );

		foreach ( $anchors as $slug => $label ) {
			$output .= sprintf( '<a class="no-pjax" href="#%s">%s</a>',
				esc_attr( $slug ), esc_html( $label ) );
		}

		do_action( 'append_anchor_navigation', $anchors );

		$output .= '</nav></div>';
	}

	return $output ?? '';
}

function the_anchor_navigation( string $post_content ) {
	echo get_anchor_navigation( $post_content );
}

/**
 * Extract id (anchor) from HTML.
 *
 * @param array|string $content
 *
 * @return array
 */
function parse_anchors( array|string $content ): array {
	$anchors = [];

	if ( is_string( $content ) ) $blocks = parse_blocks( $content );
	// I assume it is already _the_ block
	else if ( is_array( $content ) ) $blocks = [$content];
	else return $anchors;

	foreach ( $blocks as $block ) {
		if ( $block['blockName'] == 'core/file' ) continue;
		if ( str_contains( $block['attrs']['className'] ?? '', 'no-anchor' ) ) continue;

		// extract `id` (anchor) from HTML
		if ( preg_match('/<[a-zA-Z0-9]+[^>]*? id="([^"]*)"[^>]*?>/', $block['innerHTML'], $matches ) ) {
			$anchors[sanitize_title( $matches[1] )] = $matches[1];
		}

		// loop through inner blocks
		foreach ( $block['innerBlocks'] as $inner_block ) {
			$anchors += parse_anchors( $inner_block, true );
		}
	}

	return isset((func_get_args())[1])
		? $anchors ?? []
		: apply_filters( 'anchor_navigation_items', $anchors ?? [], $content );
}

// change anchor label to valid id attribute value
add_filter( 'render_block', function( string $block_content, array $block, WP_Block $instance ) {
	if (
		!str_contains( $block['attrs']['className'] ?? '', 'no-anchor' )
		&& preg_match('/ id=["\']([^"\']+)["\']/', $block['innerHTML'], $matches )
	) {
		$block_content = str_replace( $matches[0], ' id="' . sanitize_title( $matches[1] )  . '" aria-label="' . esc_attr( $matches[1] ) . '"', $block_content );
	}

	return $block_content;
}, 10, 3 );
