<?php

define( 'UTLTS_DEV_DIRECTORY', dirname( __FILE__ ) );
define( 'UTLTS_DEV_DIRECTORY_URI', UTLTS_DIRECTORY_URI . '/inc/dev' );

function isLocal() {
	return apply_filters( 'is-local', wp_get_environment_type() === 'local' );
}

function isStage() {
	// configured home URL, not the request: `SERVER_NAME` can come from the Host header (e.g. Apache `UseCanonicalName Off`)
	return apply_filters( 'is-stage', wp_get_environment_type() === 'staging'
		|| str_starts_with( (string) wp_parse_url( home_url(), PHP_URL_HOST ), 'stage.' ) );
}

function isDev() {
	return apply_filters( 'is-dev', isLocal() || isStage() );
}

function isLive() {
	return apply_filters( 'is-live', !isDev() );
}

if ( isLive() ) return;

// t9n
add_action( 'after_setup_theme', function() {
    load_theme_textdomain( 'dev', UTLTS_DEV_DIRECTORY . '/languages' );
} );

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'dev-info-panel', UTLTS_DEV_DIRECTORY_URI . '/style.css', [], false, 'async' );
	wp_enqueue_script( 'dev-info-panel', UTLTS_DEV_DIRECTORY_URI . '/app.js', [], false, ['in_footer' => true, 'strategy' => 'defer'] );
} );

add_action( 'wp_footer', function() {  ?>

    <div id="dev-info-panel">
        <?php do_action( 'dev-info-panel' ); ?>
    </div>

<?php }, 11 );

// auto include /inc files
auto_include_files( dirname( __FILE__ ) . '/inc' );
