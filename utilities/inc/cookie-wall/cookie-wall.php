<?php /**
 * Backend input (sidebar widget) for cookie wall.
 *
 * Auto-supports YouTube, Vimeo, Google Maps (see `../gmaps`).
 */

define( 'UTLTS_COOKIEWALL_DIRECTORY', dirname( __FILE__ ) );
define( 'UTLTS_COOKIEWALL_DIRECTORY_URI', UTLTS_DIRECTORY_URI . '/inc/cookie-wall' );

// t9n
add_action( 'after_setup_theme', function() {
	load_theme_textdomain( 'cookie-wall', UTLTS_COOKIEWALL_DIRECTORY . '/languages' );
} );

/**
 * Register scripts and styles.
 */
add_action( 'wp_enqueue_scripts', function() {
	if ( is_active_sidebar( 'cookie-wall' ) ) {
		wp_enqueue_script( 'cookie-wall', UTLTS_COOKIEWALL_DIRECTORY_URI . '/app.js', ['behaviours', 'alter'], false, ['in_footer' => true, 'strategy' => 'defer'] );
		wp_enqueue_style( 'cookie-wall', UTLTS_COOKIEWALL_DIRECTORY_URI . '/style.css' );
	}
}, 9 );

/**
 * Register widgets sidebar to enter cookie wall aka external content blocker message.
 */
add_action( 'widgets_init', function() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Cookie Wall', 'cookie-wall' ),
			'id'            => 'cookie-wall',
			'description'   => esc_html__( 'Add text to be displayed if the user has not yet given consent.', 'cookie-wall' ),
			'before_widget' => '<section class="cookie-wall">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
} );

/**
 * Retrieve cookie wall.
 * @return void
 */
function cookie_wall(): void {
	dynamic_sidebar( 'cookie-wall' );
}

/**
 * Display cookie wall.
 * @return string
 *
 * @since 1.20.0
 */
function get_cookie_wall(): string {
	ob_start();
	cookie_wall();
	return ob_get_clean();
}

/**
 * Add cookie wall to YouTube/Vimeo videos.
 *
 * @since 1.20.0
 */
add_filter( 'embed_oembed_html', function( $html, $url, $attr, $post_id ) {
	if ( get_video_provider( $url ) ) {
		$html .= get_cookie_wall();
	}

	return $html;
}, 10, 4 );
