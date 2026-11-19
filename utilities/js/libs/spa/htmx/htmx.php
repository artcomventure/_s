<?php

/**
 * htmx: AJAX navigation (successor of pjax, wins if both are included in `../spa.php`).
 */

define( 'UTLTS_JS_LIBS_SPA_HTMX_DIRECTORY', dirname( __FILE__ ) );
const UTLTS_JS_LIBS_SPA_HTMX_DIRECTORY_URI = UTLTS_DIRECTORY_URI . '/js/libs/spa/htmx';

// register htmx lib
add_action( 'wp_enqueue_scripts', function() {
	wp_register_script( 'htmx-module', UTLTS_JS_LIBS_SPA_HTMX_DIRECTORY_URI . '/module/htmx.min.js', [], '2.0.11', ['in_footer' => true, 'strategy' => 'defer'] );
	// preload extension (opt-in via `Alter.add( 'htmx-preload', … )` in `app.js`)
	wp_register_script( 'htmx-ext-preload', UTLTS_JS_LIBS_SPA_HTMX_DIRECTORY_URI . '/module/ext-preload.min.js', ['htmx-module'], '2.1.2', ['in_footer' => true, 'strategy' => 'defer'] );
	wp_register_script( 'htmx', UTLTS_JS_LIBS_SPA_HTMX_DIRECTORY_URI . '/app.js', ['htmx-module', 'htmx-ext-preload', 'behaviours', 'alter'], false, ['in_footer' => true, 'strategy' => 'defer'] );

	if ( utilities_spa_option_enabled() )
		wp_enqueue_script( 'htmx' );
}, 9 );

// htmx replaces pjax (both would handle the same clicks)
add_action( 'wp_enqueue_scripts', function() {
	if ( wp_script_is( 'htmx' ) ) wp_dequeue_script( 'pjax' );
}, 11 );

// preloaded pages must be cacheable, otherwise the click loads them again (logged-out only, logged-in get no-cache);
// `Vary`: no htmx response for normal navigations
add_filter( 'wp_headers', function( $headers ) {
	if ( ( $_SERVER['HTTP_HX_PRELOADED'] ?? '' ) !== 'true' || ( $_SERVER['REQUEST_METHOD'] ?? '' ) !== 'GET' || is_user_logged_in() ) return $headers;

	$max_age = (int) apply_filters( 'utilities_htmx_preload_max_age', 60 );
	if ( $max_age <= 0 ) return $headers;

	$headers['Cache-Control'] = "private, max-age={$max_age}";
	$headers['Vary'] = 'HX-Request';

	return $headers;
} );

add_action( 'wp_head', function() {
	if ( !utilities_spa_option_enabled() ) return;

	// no history cache for logged-in users: cached pages (whole `<body>`) stay in `sessionStorage` after logout
	if ( is_user_logged_in() ) {
		printf( '<meta name="htmx-config" content="%s">' . "\n", esc_attr( wp_json_encode( ['historyCacheSize' => 0] ) ) );
	}
}, 1 );
