<?php

define( 'DEV_DIRECTORY', dirname( __FILE__ ) );
define( 'DEV_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/inc/dev' );

function isLocal() {
	return apply_filters( 'is-local', wp_get_environment_type() === 'local' );
}

function isStage() {
	return apply_filters( 'is-stage', str_starts_with( $_SERVER['SERVER_NAME'] ?? '', 'stage.' ) );
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
    load_theme_textdomain( 'dev', DEV_DIRECTORY . '/languages' );
} );

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'dev-info-panel', DEV_DIRECTORY_URI . '/style.css' );
	wp_enqueue_script( 'dev-info-panel', DEV_DIRECTORY_URI . '/app.js', [], false, true );
} );

add_action( 'wp_footer', function() {  ?>

    <div id="dev-info-panel">
        <?php do_action( 'dev-info-panel' ); ?>
    </div>

<?php }, 11 );

// auto include /inc files
auto_include_files( dirname( __FILE__ ) . '/inc' );
