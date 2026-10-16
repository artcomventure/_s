<?php

define( 'UTLTS_DIRECTORY', dirname( __FILE__ ) );
define( 'UTLTS_DIRECTORY_URI', get_template_directory_uri() . '/utilities' );

// t9n
add_action( 'after_setup_theme', function() {
	load_theme_textdomain( 'utilities', UTLTS_DIRECTORY . '/languages' );
} );

add_action( 'wp_head', function() {
	// set CSS properties, _first_ in `<head>` to immediately set value
	// `--root-width` ... for CSS' fluid calculation
	// `--scrollbar-width` ... to calculate vw right ?>
<script>(() => {
	const $root = document.documentElement;
	const setProperties = () => {
		$root.style.setProperty( '--root-width', `${$root.clientWidth}` );
		$root.style.setProperty( '--scrollbar-width', `${window.innerWidth - $root.clientWidth}px` );
	};
	setProperties();
	new ResizeObserver( setProperties ).observe( $root );
})();</script>
<?php
}, -1 );

// make _utilities_ also available in admin area
add_action( 'admin_enqueue_scripts', 'utilities_register_scripts' );
function utilities_register_scripts(): void {
	wp_register_script( 'behaviours', UTLTS_DIRECTORY_URI . '/js/behaviours.js', ['alter'], '2.1.0', ['in_footer' => true, 'strategy' => 'defer'] );
	wp_register_script( 'alter', UTLTS_DIRECTORY_URI . '/js/alter.js', [], '1.1.0', ['in_footer' => true, 'strategy' => 'defer'] );
	wp_register_script( 'cache', UTLTS_DIRECTORY_URI . '/js/cache.js', [], '1.3.1', ['in_footer' => true, 'strategy' => 'defer'] );
	wp_register_script( 'bouncer', UTLTS_DIRECTORY_URI . '/js/libs/bouncer.min.js', [], '1.4.6', ['in_footer' => true, 'strategy' => 'defer'] );

	wp_register_script( 'helpers', UTLTS_DIRECTORY_URI . '/js/helpers.js', ['behaviours', 'alter'], false, ['in_footer' => true, 'strategy' => 'defer'] );
	wp_register_script( 'external-links', UTLTS_DIRECTORY_URI . '/js/external-links.js', ['behaviours', 'alter'], false, ['in_footer' => true, 'strategy' => 'defer'] );
	wp_register_script( 'inline-svg', UTLTS_DIRECTORY_URI . '/js/inline-svg.js', ['behaviours'], false, ['in_footer' => true, 'strategy' => 'defer'] );
	wp_register_script( 'custom-width', UTLTS_DIRECTORY_URI . '/js/custom-width.js', ['behaviours'], false, ['in_footer' => true, 'strategy' => 'defer'] );
	wp_register_script( 'in-viewport', UTLTS_DIRECTORY_URI . '/js/in-viewport.js', [], false, ['in_footer' => true, 'strategy' => 'defer'] );

	wp_register_style( 'admin-bar-ux', UTLTS_DIRECTORY_URI . '/css/admin-bar.css' );
	wp_register_script( 'css-breakpoints', UTLTS_DIRECTORY_URI . '/js/BREAKPOINTS.js', [], false, ['in_footer' => true, 'strategy' => 'defer'] );
}

add_action( 'wp_enqueue_scripts', function() {
	utilities_register_scripts();

	wp_enqueue_script( 'helpers' );
	wp_enqueue_script( 'external-links' );
	wp_enqueue_script( 'inline-svg' );
	wp_enqueue_script( 'custom-width' );
	wp_enqueue_script( 'in-viewport' );

	if ( wp_script_is( 'admin-bar' ) ) wp_enqueue_style( 'admin-bar-ux' );
	wp_enqueue_script( 'css-breakpoints' );
}, 9 );

add_action( 'admin_enqueue_scripts', function() {
	wp_enqueue_style( 'utilities-admin', UTLTS_DIRECTORY_URI . '/css/admin.css' );
} );

// auto include utilities files
require_once UTLTS_DIRECTORY . '/auto-include-files.php';
auto_include_files( UTLTS_DIRECTORY . '/js/polyfills' );
auto_include_files( UTLTS_DIRECTORY . '/inc' );
auto_include_files( UTLTS_DIRECTORY . '/js/file-drop' );
auto_include_files( UTLTS_DIRECTORY . '/js/libs' );
auto_include_files( UTLTS_DIRECTORY . '/inc/plugins' );
// auto include theme's /inc files
// TODO: theme vs child theme
auto_include_files( get_template_directory() . '/inc' );
