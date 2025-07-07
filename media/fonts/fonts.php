<?php

define( 'FONTS_DIRECTORY', dirname( __FILE__ ) );
define( 'FONTS_DIRECTORY_URI', get_template_directory_uri() . '/media/fonts' );

define( 'TYPEKIT_FONTS', apply_filters( 'typekit-font-url', '' ) );

/**
 * Enqueue fonts.
 */
add_action( 'wp_enqueue_scripts', 'enqueue_fonts' );
add_action( 'enqueue_block_editor_assets', 'enqueue_fonts' );
function enqueue_fonts(): void {
	if ( TYPEKIT_FONTS ) wp_enqueue_style( 'typekit-fonts', TYPEKIT_FONTS );
	wp_enqueue_style( 'icont', FONTS_DIRECTORY_URI . '/Icont/font.css' );
}

/**
 * Preload woff2 fonts.
 */
//add_action( 'wp_head', function() {
//	// _loop_ to get child directories
//	function preload( $dir, $folder ) {
//		$files = opendir( $dir );
//
//		while ( ($file = readdir( $files )) !== false ) {
//			if ( preg_match( '/^\.+$/', $file ) ) continue;
//			if ( is_dir( "$dir/$file" ) ) {
//				preload( "$dir/$file", "$folder/$file" );
//			}
//			else if ( str_ends_with( $file, '.woff2' ) ) {
//				echo "\n" . '<link rel="preload" href="' . FONTS_DIRECTORY_URI . $folder . '/' . $file . '" as="font" type="font/woff2" crossorigin />';
//			}
//		}
//
//		closedir( $files );
//	}
//
//	preload( FONTS_DIRECTORY, '' );
//} );
