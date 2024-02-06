<?php

/**
 * Auto-include of all PHP files of a given directory-`$path`.
 * Default `$path` is the theme's `/inc/` directory.
 *
 * Version: 1.0.1
 *
 * `{$path}/FILE.php`
 * `{$path}/DIR/DIR.php`
 *
 * TODO: theme vs child theme
 *
 * @param string $path
 */

// auto-include $path's files
function auto_include_files( $path = '' ) {
	// themes ´inc´ folder is default
	if ( !$path ) $path = $path = get_template_directory() . '/inc';
	if ( !is_dir( $path ) ) return;

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

// initial auto-include `/inc/` files
auto_include_files();
