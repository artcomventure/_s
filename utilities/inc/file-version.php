<?php

/**
 * Set file's timestamp as `ver` to automatically _busting_ browser's cache.
 *
 * Runs right before each output (head, footer, late styles), so files enqueued late are covered too.
 */
add_filter( 'print_scripts_array', function( $handles ) {
	filemtime_as_file_version( wp_scripts(), $handles );
	return $handles;
}, 2 );

add_filter( 'print_styles_array', function( $handles ) {
	filemtime_as_file_version( wp_styles(), $handles );
	return $handles;
}, 2 );

/**
 * @param WP_Dependencies|null $deps    `null`: scripts and styles
 * @param array|null           $handles `null`: all registered
 */
function filemtime_as_file_version( ?WP_Dependencies $deps = null, ?array $handles = null ): void {
	static $done = [];

	foreach ( $deps ? [ $deps ] : [ wp_styles(), wp_scripts() ] as $files ) {
		foreach ( $handles ?? array_keys( $files->registered ) as $handle ) {
			$key = spl_object_id( $files ) . ":$handle";
			if ( isset( $done[ $key ] ) || !isset( $files->registered[ $handle ] ) ) continue;
			$done[ $key ] = true;

			$file = $files->registered[ $handle ];

			// version already given
			if ( $file->ver || !is_string( $file->src ) ) continue;

			$file->ver = get_file_version( $file->src );
		}
	}
}

/**
 * Get file's version number from change time.
 *
 * @param string $src
 *
 * @return string
 */
function get_file_version( string $src ): string {
	$path = get_local_asset_path( $src );

	return $path ? (string) filemtime( $path ) : '';
}

/**
 * Local file path of an asset URL (absolute, protocol-relative or relative to `site_url()`).
 *
 * @since 1.21.0
 */
function get_local_asset_path( string $src ): ?string {
	$url = explode( '?', $src )[0];
	if ( $url === '' ) return null;

	// relative to `site_url()`, like `WP_Dependencies::$base_url`
	if ( !preg_match( '#^(https?:)?//#', $url ) ) $url = site_url( $url );

	$strip = fn( $url ) => trailingslashit( preg_replace( '#^https?:#', '', $url ) );
	$url   = preg_replace( '#^https?:#', '', $url );

	// most specific first
	foreach ( [
		[ plugins_url(), WP_PLUGIN_DIR ],
		[ content_url(), WP_CONTENT_DIR ],
		[ includes_url(), ABSPATH . WPINC ],
		[ site_url(), ABSPATH ],
	] as [ $base_url, $dir ] ) {
		$base_url = $strip( $base_url );
		if ( !str_starts_with( $url, $base_url ) ) continue;

		$path = trailingslashit( $dir ) . substr( $url, strlen( $base_url ) );

		return is_file( $path ) ? $path : null;
	}

	return null;
}
