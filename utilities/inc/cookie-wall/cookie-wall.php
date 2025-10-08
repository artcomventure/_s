<?php

/**
 * Use `dynamic_sidebar( 'cookie-wall' );` to get HTML.
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
	wp_enqueue_script( 'cookie-wall', COOKIE_WALL_DIRECTORY_URI . '/app.js', ['behaviours', 'alter'], false, true );
	wp_enqueue_style( 'cookie-wall', COOKIE_WALL_DIRECTORY_URI . '/style.css' );
} );

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
 * Retrieve cookie wall text.
 *
 * @return void
 */
function cookie_wall(): void {
	dynamic_sidebar( 'cookie-wall' );
}
