<?php

define( 'UTLTS_PERFORMANCE_MINIFY_DIRECTORY', dirname( __FILE__ ) );
const UTLTS_PERFORMANCE_MINIFY_DIRECTORY_URI = UTLTS_PERFORMANCE_DIRECTORY_URI . '/inc/minify';

/**
 * Try to replace CSS and js files with minimized versions.
 *
 * Minimized version must be in same directory and must pass the following naming convention:
 * - not minimized file name: FILENAME.css
 * - minimized file name: FILENAME.min.css
 *
 * Runs right before each output (head, footer, late styles), so files enqueued late are covered too.
 */
add_filter( 'print_scripts_array', function( $handles ) {
	// frontend only
	if ( !is_admin() ) minify_replace_sources( wp_scripts(), $handles );
	return $handles;
}, 1 );

add_filter( 'print_styles_array', function( $handles ) {
	if ( !is_admin() ) minify_replace_sources( wp_styles(), $handles );
	return $handles;
}, 1 );

/**
 * @param WP_Dependencies|null $deps    `null`: scripts and styles
 * @param array|null           $handles `null`: all registered
 */
function minify_replace_sources( ?WP_Dependencies $deps = null, ?array $handles = null ): void {
	static $done = [];

	foreach ( $deps ? [ $deps ] : [ wp_styles(), wp_scripts() ] as $files ) {
		foreach ( $handles ?? array_keys( $files->registered ) as $handle ) {
			$key = spl_object_id( $files ) . ":$handle";
			if ( isset( $done[ $key ] ) || !isset( $files->registered[ $handle ] ) ) continue;
			$done[ $key ] = true;

			$file = $files->registered[ $handle ];

			// already minimized
			if ( !is_string( $file->src ) || !preg_match( '/(?<!\.min)\.(css|js)$/', $file->src ) ) continue;

			// same URL, only the extension changes (keeps subdirectory installs working)
			$minimized = preg_replace( '/\.(css|js)$/', '.min.$1', $file->src );

			// local and existing
			if ( get_local_asset_path( $minimized ) ) $file->src = $minimized;
		}
	}
}

// load library on first use only
function minify_load_library(): void {
	require_once UTLTS_PERFORMANCE_MINIFY_DIRECTORY . '/src/Minify.php';
	require_once UTLTS_PERFORMANCE_MINIFY_DIRECTORY . '/src/JS.php';
	require_once UTLTS_PERFORMANCE_MINIFY_DIRECTORY . '/src/CSS.php';
	require_once UTLTS_PERFORMANCE_MINIFY_DIRECTORY . '/path-converter/ConverterInterface.php';
	require_once UTLTS_PERFORMANCE_MINIFY_DIRECTORY . '/path-converter/Converter.php';
}

/**
 * @param string $string
 *
 * @return string
 */
function minify_css( string $string ) : string {
	minify_load_library();
	$minifier = new MatthiasMullie\Minify\CSS( $string );
	return $minifier->minify();
}

/**
 * @param string $string
 *
 * @return string
 */
function minify_js( string $string ) : string {
	minify_load_library();
	$minifier = new MatthiasMullie\Minify\JS( $string );
	return $minifier->minify();
}
