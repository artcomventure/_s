<?php /**
 * Backend input (sidebar widget) for cookie wall.
 *
 * Auto-supports YouTube, Vimeo, Google Maps (see `../gmaps`).
 */

define( 'COOKIE_WALL_DIRECTORY', dirname( __FILE__ ) );
define( 'COOKIE_WALL_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/inc/cookie-wall' );

// t9n
add_action( 'after_setup_theme', function() {
	load_theme_textdomain( 'cookie-wall', COOKIE_WALL_DIRECTORY . '/languages' );
} );

/**
 * Register scripts and styles.
 */
add_action( 'wp_enqueue_scripts', function() {
	if ( is_active_sidebar( 'cookie-wall' ) ) {
		wp_enqueue_script( 'cookie-wall', COOKIE_WALL_DIRECTORY_URI . '/app.js', ['behaviours', 'alter'], false, true );
		wp_enqueue_style( 'cookie-wall', COOKIE_WALL_DIRECTORY_URI . '/style.css' );
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
add_filter( 'wp_video_shortcode', function( $output, $atts ) {
	$oEmbed = _wp_oembed_get_object();
	if ( $video = $oEmbed->fetch( $oEmbed->get_provider( $atts['src'] ), $atts['src'] ) ) {
		if ( in_array( $video->provider_name, ['YouTube', 'Vimeo'] ) ) {
			if ( $cookie_wall = get_cookie_wall() )
				$output = preg_replace( '/<\/div>\s*$/', $cookie_wall . '</div>', $output );
		}
	}

	return $output;
}, 10, 2 );
