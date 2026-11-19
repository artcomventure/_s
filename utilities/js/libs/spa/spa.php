<?php

/**
 * JavaScript libraries for a single page application (SPA).
 */

define( 'UTLTS_JS_LIBS_SPA_DIRECTORY', dirname( __FILE__ ) );
const UTLTS_JS_LIBS_SPA_DIRECTORY_URI = UTLTS_DIRECTORY_URI . '/js/libs/spa';

// AJAX navigation switched on (customizer), not in the customizer preview
function utilities_spa_option_enabled(): bool {
	return (bool) get_option( 'utlts_spa' ) && !is_customize_preview();
}

// SPA libs (pjax will be replaced by htmx, htmx wins if both are included)
auto_include_files( UTLTS_JS_LIBS_SPA_DIRECTORY . '/pjax' );
//auto_include_files( UTLTS_JS_LIBS_SPA_DIRECTORY . '/htmx' );

// library used for AJAX navigation
function utilities_spa_library(): string {
	return defined( 'UTLTS_JS_LIBS_SPA_HTMX_DIRECTORY' ) ? 'htmx' : 'pjax';
}

// migrate options `pjax`/`htmx`/`spa` (pre-release name) to `utlts_spa` (checked via autoloaded options, no extra query)
add_action( 'init', function() {
	$options = wp_load_alloptions();
	$legacy = array_intersect_key( $options, array_flip( ['pjax', 'htmx', 'spa'] ) );
	if ( !$legacy ) return;

	if ( array_filter( $legacy ) ) update_option( 'utlts_spa', '1' );
	foreach ( array_keys( $legacy ) as $name ) delete_option( $name );
}, 0 );

// only styles of blocks used on the page (default for classic themes is `false`), pjax/htmx add missing ones of switched pages
add_filter( 'should_load_separate_core_block_assets', '__return_true' );
//add_filter( 'should_load_block_assets_on_demand', '__return_false' );

// no speculative loading: pages are fetched by the SPA lib, prefetched/prerendered pages would be loaded twice
add_filter( 'wp_speculation_rules_configuration', fn( $config ) => utilities_spa_option_enabled() ? null : $config );

// enable/disable AJAX navigation customizer setting
add_action( 'customize_register', function( $wp_customize ) {
	$wp_customize->add_setting( 'utlts_spa', [
		'type' => 'option',
		'capability' => 'manage_options',
		'sanitize_callback' => 'rest_sanitize_boolean',
	] );

	$wp_customize->add_control( 'utlts_spa', [
		'label' => __( 'Enable AJAX navigation', 'utilities' ),
		'description' => __( "Load page content using AJAX without reloading your page's layout.", 'utilities' )
			/* translators: %s: name of the JavaScript library */
			. ' ' . sprintf( __( 'Library: %s', 'utilities' ), utilities_spa_library() === 'htmx' ? 'htmx' : 'Pjax' ),
		'section' => 'utilities-ui',
		'type' => 'checkbox'
	] );
}, 9 );

// AJAX navigation active (after the libs enqueued their scripts, htmx dequeues pjax at 11)
function utilities_spa_enabled(): bool {
	return wp_script_is( 'pjax' ) || wp_script_is( 'htmx' );
}

// page transition overlay (`#spa-transition`, `body[data-spa-transition]`)
add_action( 'wp_enqueue_scripts', function() {
	if ( !utilities_spa_enabled() ) return;

	// DON'T load async because the scripts read its `transition-duration`
	wp_enqueue_style( 'spa', UTLTS_JS_LIBS_SPA_DIRECTORY_URI . '/style.css' );
	if ( $hex = get_background_color() ) {
		wp_add_inline_style( 'spa', "body.custom-background #spa-transition { background-color: #{$hex}; }" );
	}
}, 12 );

// outside the swapped elements, before the footer scripts
add_action( 'wp_footer', function() {
	if ( utilities_spa_enabled() ) echo '<div id="spa-transition" style="display: none;" aria-hidden="true"></div>' . "\n";
}, 5 );