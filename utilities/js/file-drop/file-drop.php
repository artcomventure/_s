<?php

define( 'FILE_DROP_DIRECTORY', dirname( __FILE__ ) );
define( 'FILE_DROP_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/js/file-drop' );

// register file drop script
add_action( 'wp_enqueue_scripts', function() {
	wp_register_script( 'file-drop', FILE_DROP_DIRECTORY_URI . '/app.js', ['alter', 'wp-i18n'], false, true );
	wp_set_script_translations( 'file-drop', 'file-drop', FILE_DROP_DIRECTORY . '/languages/' );
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
		wp_enqueue_style( 'file-drop', FILE_DROP_DIRECTORY_URI . '/style.css' );
	}
}, 1982 );
