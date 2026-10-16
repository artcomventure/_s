<?php

/**
 * Finds code that may break since utilities' scripts are loaded with `defer` (1.21).
 *
 * Usage (WordPress container, WP root):
 * `wp eval-file wp-content/themes/THEME/utilities/bin/check-defer.php --allow-root`
 *
 * Heuristic: reports usages of utilities' globals, not whether they run immediately.
 * Usage inside `Behaviours.add()`/`DOMContentLoaded` callbacks is fine.
 *
 * @since 1.21.0
 */

if ( !defined( 'WP_CLI' ) ) exit;

// global (regex) => handle providing it
$globals = [
	'Behaviours'                                                                            => 'behaviours',
	'Alter'                                                                                 => 'alter',
	'Bouncer'                                                                               => 'bouncer',
	'BREAKPOINTS'                                                                           => 'css-breakpoints',
	'(?:utlts\.)?(?:getBackgroundColor|isMobileDevice|isTouchDevice|isNotTouchDevice|uuid)' => 'helpers',
	'(?:utlts\.)?(?:isInternalUrl|isExternalUrl)'                                           => 'external-links',
	'utlts\.(?:inViewportObserver|isInViewport|isNotInViewport|isOutsideViewport)'          => 'in-viewport',
	'gsap'                                                                                  => 'gsap',
	'ScrollTrigger'                                                                         => 'gsap-ScrollTrigger',
	'Flip'                                                                                  => 'gsap-Flip',
	'Draggable'                                                                             => 'gsap-Draggable',
	'Observer'                                                                              => 'gsap-Observer',
	'CustomEase'                                                                            => 'gsap-CustomEase',
	'Pjax'                                                                                  => 'pjax-module',
	'autosize'                                                                              => 'autosize-module',
	'Tablesort'                                                                             => 'tablesort',
	'TomSelect'                                                                             => 'tom-select',
	'lottie'                                                                                => 'lottie-web',
	'AirDatepicker'                                                                         => 'air-datepicker',
	'Odometer'                                                                              => 'odometer-module',
	'videojs'                                                                               => 'video-js',
	'customSelect'                                                                          => 'custom-select-module',
];

$theme_dir     = wp_normalize_path( get_template_directory() );
$utilities_dir = wp_normalize_path( UTLTS_DIRECTORY );
$issues        = 0;

$relative = fn( string $path ) => ltrim( str_replace( $theme_dir, '', wp_normalize_path( $path ) ), '/' );

// without comments, so commented code isn't reported
$strip_comments = fn( string $code ) => preg_replace( ['#/\*.*?\*/#s', '#(?<![:"\'\\\\])//[^\n]*#'], '', $code );

/**
 * Globals used in `$code` => providing handle. `typeof X` guarded globals are optional and skipped.
 */
$used_globals = function( string $code ) use ( $globals, $strip_comments ): array {
	$code  = $strip_comments( $code );
	$found = [];

	foreach ( $globals as $pattern => $handle ) {
		if ( !preg_match_all( "/(?<![\\w$.'\"-])($pattern)\\b(?!\\s*:)/", $code, $matches ) ) continue;

		foreach ( array_unique( $matches[1] ) as $name ) {
			$base = preg_replace( '/^utlts\./', '', $name );
			if ( preg_match( '/typeof\s+(window\.)?' . preg_quote( $base, '/' ) . '\b/', $code ) ) continue;

			$found[ $name ] = $handle;
		}
	}

	return $found;
};

// register all frontend scripts
do_action( 'wp_enqueue_scripts' );
$scripts = wp_scripts();

$all_deps = function( string $handle, array &$seen = [] ) use ( &$all_deps, $scripts ): array {
	foreach ( $scripts->registered[ $handle ]->deps ?? [] as $dep ) {
		if ( isset( $seen[ $dep ] ) ) continue;
		$seen[ $dep ] = true;
		$all_deps( $dep, $seen );
	}

	return array_keys( $seen );
};

$is_deferred = fn( string $handle ) => in_array( $scripts->get_data( $handle, 'strategy' ), ['defer', 'async'], true );

WP_CLI::log( WP_CLI::colorize( '%B1. Theme scripts: missing dependencies, blocking scripts%n' ) );

$checked_files = [];

foreach ( $scripts->registered as $handle => $script ) {
	if ( !is_string( $script->src ) || !$script->src ) continue;

	$path = get_local_asset_path( $script->src );
	if ( !$path || !str_starts_with( wp_normalize_path( $path ), $theme_dir ) ) continue;

	$checked_files[ wp_normalize_path( $path ) ] = true;

	// vendor libs and editor bundles
	if ( str_ends_with( $path, '.min.js' ) || str_contains( $path, '/build/' ) ) continue;

	$deps = $all_deps( $handle );
	foreach ( $used_globals( (string) file_get_contents( $path ) ) as $name => $provider ) {
		if ( $provider === $handle || in_array( $provider, $deps, true ) ) continue;
		// script provides the global itself
		if ( isset( $scripts->registered[ $provider ] ) && $scripts->registered[ $provider ]->src === $script->src ) continue;

		$issues++;
		WP_CLI::log( sprintf( '  %s (%s): uses `%s` → add dependency `%s`', $handle, $relative( $path ), $name, $provider ) );
	}

	// blocking script depending on deferred ones → WP silently loads the whole chain blocking
	if ( !$is_deferred( $handle ) && !str_starts_with( wp_normalize_path( $path ), $utilities_dir ) ) {
		$deferred = array_filter( $deps, $is_deferred );
		if ( $deferred ) {
			$issues++;
			WP_CLI::log( sprintf( '  %s (%s): no `defer` → `%s` load blocking too; enqueue with `[\'in_footer\' => true, \'strategy\' => \'defer\']`', $handle, $relative( $path ), implode( '`, `', $deferred ) ) );
		}
	}
}

WP_CLI::log( WP_CLI::colorize( '%B2. Inline scripts in theme PHP files (outside utilities/)%n' ) );

$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $theme_dir, FilesystemIterator::SKIP_DOTS ) );
$js_files = [];

foreach ( $files as $file ) {
	$path = wp_normalize_path( $file->getPathname() );
	if ( preg_match( '#/(node_modules|vendor|build)/#', $path ) || str_starts_with( $path, $utilities_dir ) ) continue;

	if ( preg_match( '/(?<!\.min)\.js$/', $path ) ) $js_files[] = $path;
	if ( !str_ends_with( $path, '.php' ) ) continue;

	$code = (string) file_get_contents( $path );

	// `<script>` without `src` and `wp_add_inline_script()` calls
	preg_match_all( '#<script\b(?![^>]*\bsrc=)[^>]*>(.*?)</script>|wp_add_inline_script\s*\((.*?)\);#s', $code, $blocks, PREG_SET_ORDER | PREG_OFFSET_CAPTURE );
	foreach ( $blocks as $block ) {
		$inline = $block[1][0] ?? '' ?: ( $block[2][0] ?? '' );
		if ( !$globals_found = $used_globals( $inline ) ) continue;

		$issues++;
		$line = substr_count( substr( $code, 0, $block[0][1] ), "\n" ) + 1;
		WP_CLI::log( sprintf( '  %s:%d uses `%s` → wrap in `DOMContentLoaded` or move into a script with dependency', $relative( $path ), $line, implode( '`, `', array_keys( $globals_found ) ) ) );
	}
}

WP_CLI::log( WP_CLI::colorize( '%B3. Inline scripts in post content%n' ) );

global $wpdb;
$posts = $wpdb->get_results( "SELECT ID, post_type, post_content FROM {$wpdb->posts} WHERE post_status IN ('publish', 'private', 'draft', 'future') AND post_content LIKE '%<script%'" );
foreach ( $posts as $post ) {
	preg_match_all( '#<script\b(?![^>]*\bsrc=)[^>]*>(.*?)</script>#s', $post->post_content, $blocks );
	$globals_found = $used_globals( implode( "\n", $blocks[1] ) );
	if ( !$globals_found ) continue;

	$issues++;
	WP_CLI::log( sprintf( '  %s #%d uses `%s`', $post->post_type, $post->ID, implode( '`, `', array_keys( $globals_found ) ) ) );
}

WP_CLI::log( WP_CLI::colorize( '%B4. Not checked: theme JS files not registered on `wp_enqueue_scripts` (e.g. enqueued conditionally) – check manually%n' ) );

foreach ( $js_files as $path ) {
	if ( !isset( $checked_files[ $path ] ) ) WP_CLI::log( '  ' . $relative( $path ) );
}

$issues
	? WP_CLI::warning( "$issues possible issue(s). Then test pages with an open browser console." )
	: WP_CLI::success( 'Nothing found. Still test pages with an open browser console.' );
