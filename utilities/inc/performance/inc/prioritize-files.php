<?php

/**
 * `fetchpriority` is a hint to the browser indicating how it should prioritize fetching
 * a file (img, css, js, font, iframe) relative to other files.
 */
add_filter( 'wp_preload_resources', function( $resources ) {
	// font styles; resolve final `src`/`ver` now, so preload matches the printed URLs
	$fonts = array_keys( array_filter( wp_styles()->registered, fn( $file ) => is_string( $file->src ) && str_contains( $file->src, '/font.css' ) ) );
	minify_replace_sources( wp_styles(), $fonts );
	filemtime_as_file_version( wp_styles(), $fonts );

	// theme CSS
	$resources[] = [
		'href' => get_stylesheet_uri() . '?ver=' . get_file_version( get_stylesheet_uri() ),
		'as' => 'style',
		'type' => 'text/css',
		'fetchpriority' => 'high'
	];

	// fonts @since 1.20.2
	foreach ( $fonts as $handle ) {
		$file = wp_styles()->registered[ $handle ];
		$href = $file->src;
		if ( $file->ver ) $href .= "?ver=$file->ver";

		$resources[] = [
			'href' => $href,
			'as' => 'style',
			'type' => 'text/css',
			'fetchpriority' => 'high'
		];
	}

	return $resources;
} );

/**
 * Preload jQuery scripts to optimize the page load.
 * Thx to https://www.crafted.at/b/wordpress-jquery-ladevorgang-optimieren/#ladezeiten-coding
 *
 * @since 1.20.0
 */
add_action( 'wp_enqueue_scripts', function() {
	if ( wp_script_is( 'jquery' ) ) {
		add_filter( 'wp_preload_resources', function( array $preload_resources ): array {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			// get jQuery script
			$wp_jquery_ver  = wp_scripts()->registered['jquery-core']->ver;
			$path_to_jquery = includes_url( "js/jquery/jquery$suffix.js?ver=$wp_jquery_ver" );

			$preload_resources[] = array(
				'href' => $path_to_jquery,
				'as'   => 'script',
			);

			return $preload_resources;
		} );

		if ( wp_script_is( 'jquery-migrate' ) ) {
			add_filter( 'wp_preload_resources', function( array $preload_resources ): array {
				$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

				// get jQuery migrate script
				$wp_jquery_migrate_ver  = wp_scripts()->registered['jquery-migrate']->ver;
				$path_to_jquery_migrate = includes_url( "js/jquery/jquery-migrate$suffix.js?ver=$wp_jquery_migrate_ver" );

				$preload_resources[] = array(
					'href' => $path_to_jquery_migrate,
					'as'   => 'script',
				);

				return $preload_resources;
			} );
		}
	}
} );

/**
 * I assume no one will ever print _this_ webpage.
 * So we use the `media="print"` attribute for asynchronously load CSS files.
 * @see https://www.filamentgroup.com/lab/load-css-simpler/
 *
 * Usage:
 * `wp_enqueue_style( HANDLE, URI, DEPENDENCIES, VERSION, 'async' )`
 *
 * @since 1.20.2
 */
add_filter( 'style_loader_tag', function( $tag, $handle, $href, $media ) {
	if ( 'async' === $media && !str_contains( $tag, ' onload="' ) ) {
		$tag = str_replace(" media='async'", ' media="print" onload="this.media=\'all\';this.onload=null;"', $tag);
	}

	return $tag;
}, 10, 4 );

/**
 * Set `loading="eager"` and/or `fetchpriority="height"` through classes.
 *
 * @since 1.20.11
 */
add_filter( 'render_block', function( $block_content, $block ) {
	$allowed_blocks = ['core/image', 'core/cover', 'core/media-text'];

	if ( isset( $block['blockName'] ) && in_array( $block['blockName'], $allowed_blocks, true ) && ! empty( $block['attrs']['className'] ) ) {
		$classes = explode( ' ', $block['attrs']['className'] );

		// loading attribute
		if ( in_array( 'no-lazy', $classes, true ) || in_array( 'loading-eager', $classes, true ) ) {
			if ( strpos( $block_content, 'loading="lazy"' ) !== false ) {
				$block_content = str_replace( 'loading="lazy"', 'loading="eager"', $block_content );
			} else {
				$block_content = str_replace( '<img ', '<img loading="eager" ', $block_content );
			}
		}

		// fetchpriority attribute
		if ( in_array( 'fetchpriority-high', $classes, true ) ) {
			if ( strpos( $block_content, 'fetchpriority=' ) === false ) {
				$block_content = str_replace( '<img ', '<img fetchpriority="high" ', $block_content );
			}
		}

		// clean up
		$classes_to_remove = ['no-lazy', 'loading-eager', 'fetchpriority-high'];
		$block_content = preg_replace_callback('/class="([^"]+)"/', function( $matches ) use ( $classes_to_remove ) {
			$current_classes = explode( ' ', $matches[1] );
			$filtered_classes = array_diff( $current_classes, $classes_to_remove );

			if ( ! empty( $filtered_classes ) ) {
				return 'class="' . implode( ' ', array_map( 'trim', $filtered_classes ) ) . '"';
			}

			return '';
		}, $block_content);
	}

	return $block_content;
}, 10, 2 );
