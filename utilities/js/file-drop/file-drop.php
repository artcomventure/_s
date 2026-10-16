<?php

define( 'UTLTS_JS_FILEDROP_DIRECTORY', dirname( __FILE__ ) );
define( 'UTLTS_JS_FILEDROP_DIRECTORY_URI', UTLTS_DIRECTORY_URI . '/js/file-drop' );

// register file drop script
add_action( 'wp_enqueue_scripts', function() {
	wp_register_script( 'file-drop', UTLTS_JS_FILEDROP_DIRECTORY_URI . '/app.js', ['behaviours', 'alter', 'wp-i18n'], false, ['in_footer' => true, 'strategy' => 'defer'] );
	wp_set_script_translations( 'file-drop', 'file-drop', UTLTS_JS_FILEDROP_DIRECTORY . '/languages/' );
} );

// change i18n json file name to `LOCALE-HASH.json` like created by `wp i18n make-json`
add_filter( 'load_script_translation_file', function( $file, $handle, $domain ) {
	if ( $file && $domain == 'file-drop' && $handle == 'file-drop' ) {
		$md5 = md5('app.js');
		$file = preg_replace( '/\/' . $domain . '-([^-]+)-.+\.json$/', "/$1-{$md5}.json", $file );
	}

	return $file;
}, 10, 3 );

// add default file drop styles
add_action( 'wp_enqueue_scripts', function() {
	if ( wp_script_is('file-drop' ) ) {
		wp_enqueue_style( 'file-drop', UTLTS_JS_FILEDROP_DIRECTORY_URI . '/style.css' );
	}
}, 1982 );
