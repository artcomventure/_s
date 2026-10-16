<?php

define( 'UTLTS_RESTAPI_DIRECTORY', dirname( __FILE__ ) );
define( 'UTLTS_RESTAPI_DIRECTORY_URI', UTLTS_DIRECTORY_URI . '/inc/rest-api' );

// JS client for `inc/l10n.php`: add `utilities-l10n` as dependency to use it
add_action( 'init', function() {
	wp_register_script( 'utilities-l10n', UTLTS_RESTAPI_DIRECTORY_URI . '/inc/app.js', ['wp-api-fetch', 'wp-url'], false, ['in_footer' => true, 'strategy' => 'defer'] );
} );

// auto include /inc files
auto_include_files( dirname( __FILE__ ) . '/inc' );
