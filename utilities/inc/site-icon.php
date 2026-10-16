<?php

/**
 * Fallback favicon.
 *
 * @since 1.21
 */

add_action( 'init', function () {
	// disable: `add_filter( 'utlts_site_icon', '__return_false' )`
	if ( ! apply_filters( 'utlts_site_icon', ! has_site_icon() ) ) return;

	$output = function (): void {
		if ( is_customize_preview() ) return;

		$name  = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
		$words = preg_split( '/[\s\-_]+/u', $name, -1, PREG_SPLIT_NO_EMPTY );

		// keep alphanumeric chars
		$words = array_values( array_filter( array_map(
			fn( $w ) => preg_replace( '/[^\p{L}\p{N}]/u', '', $w ),
			$words
		) ) );

		$initials = match ( count( $words ) ) {
			0       => '?',
			1       => mb_substr( $words[0], 0, 2 ),
			default => mb_substr( $words[0], 0, 1 ) . mb_substr( $words[1], 0, 1 ),
		};
		$initials = apply_filters( 'utlts_site_icon_initials', mb_strtoupper( $initials ) );

		// gradient start/end
		[$from, $to] = apply_filters( 'utlts_site_icon_colors', ['#e81cff', '#40c9ff'] );

		$svg = sprintf(
			'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">'
			. '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">'
			. '<stop offset="0" stop-color="%s"/><stop offset="1" stop-color="%s"/>'
			. '</linearGradient></defs>'
			. '<rect width="32" height="32" rx="6" fill="url(#g)"/>'
			. '<text x="50%%" y="50%%" dominant-baseline="central" text-anchor="middle" '
			. 'font-family="system-ui,-apple-system,Segoe UI,Roboto,sans-serif" '
			. 'font-size="16" font-weight="700" fill="#fff">%s</text>'
			. '</svg>',
			esc_attr( $from ),
			esc_attr( $to ),
			htmlspecialchars( $initials, ENT_XML1 | ENT_QUOTES, 'UTF-8' )
		);

		printf( '<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%s">' . "\n", rawurlencode( $svg ) );
	};

	foreach ( ['wp_head', 'admin_head', 'login_head'] as $hook ) {
		add_action( $hook, $output, 1 );
	}
} );
