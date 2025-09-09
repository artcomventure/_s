<?php

/**
 * Set file's timestamp as `ver` to automatically _busting_ browser's cache.
 */
add_action( 'wp_enqueue_scripts', 'filemtime_as_file_version', 12 );
add_action( 'admin_enqueue_scripts', 'filemtime_as_file_version', 12 );
function filemtime_as_file_version(): void {
	// all CSS and js files
	foreach ( array( wp_styles(), wp_scripts() ) as $files ) {
		foreach ( $files->registered as $file ) {
			// version already given
			if ( $file->ver ) continue;

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
	$home_url  = preg_quote( get_site_url(), '/' );

	// is no local file
	if ( ! preg_match( '/^(\/|' . $home_url . ')/', $src ) )
		return '';

	return filemtime( rtrim( ABSPATH, '/' ) . wp_make_link_relative( explode( '?', $src )[0] ) );
}
