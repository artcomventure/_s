<?php

/**
 * Auto-include of all PHP files of a given directory-`$path`.
 *
 * Usage:
 * `auto_include_files( PATH )`
 *
 * Lookup:
 * `{PATH}/FILE.php`
 * `{PATH}/DIR/DIR.php`
 *
 * @param string $path
 */
function auto_include_files( string $path ): void {
	if ( !is_dir( $path ) ) return;

	$path = rtrim( $path, '/' );

	if ( $inc = opendir( $path ) ) {
		while ( ($file = readdir( $inc )) !== false ) {
			// ignore files/folders which starts with '_'
			if ( strpos( $file, '_' ) === 1 ) continue;

			$filename = "{$path}/{$file}";

			// don't include _this_ file again
			if ( __FILE__ == $filename ) continue;

			// auto include `FOLDER/FOLDER.php`
			if ( is_dir( $filename ) ) {
				if ( preg_match( '/^\.+$/', $file ) ) continue;
				if ( !file_exists( $filename .= "/{$file}.php" ) ) continue;
			}
			// `$file` is first level file
			elseif ( !preg_match( '/\.php$/', $file ) ) continue;

			require_once $filename;
		}

		closedir( $inc );
	}
}
