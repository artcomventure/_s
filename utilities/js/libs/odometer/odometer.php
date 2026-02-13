<?php

add_action( 'wp_enqueue_scripts', function () {
	wp_register_script( 'odometer-module', LIBS_DIRECTORY_URI . '/odometer/module/odometer.js', [], '0.4.7', true );
	wp_register_script( 'odometer', LIBS_DIRECTORY_URI . '/odometer/app.js', ['odometer-module'], false, true );

	// https://github.hubspot.com/odometer/#advanced
	if ( $odometerOptions = apply_filters( 'odometerOptions', ['auto' => false] ) )
		wp_add_inline_script( 'odometer-module', 'window.odometerOptions = ' . json_encode( $odometerOptions ) . ';', 'before' );

	// register themes
	foreach ( ['car', 'default', 'digital', 'minimal', 'plaza', 'slot-machine', 'train-station'] as $theme ) {
		$handle = "odometer-theme-$theme";
		wp_register_style( $handle, LIBS_DIRECTORY_URI . "/odometer/module/themes/$handle.css", [], '0.4.7' );

		$theme_enqueued = ($theme_enqueued ?? false) || wp_style_is( $handle );
	}

	// auto enqueue default styles
	if ( wp_script_is('odometer' ) && !$theme_enqueued )
		wp_enqueue_style( 'odometer-theme-default' );

	if ( wp_style_is( 'odometer-theme-default' ) ) {
		wp_add_inline_style( 'odometer-theme-default', ".odometer.odometer-auto-theme {
			font-family: inherit;
			line-height: inherit;
		}

		.odometer.odometer-auto-theme,
		.odometer.odometer-auto-theme .odometer-digit,
		.odometer.odometer-auto-theme .odometer-digit .odometer-digit-spacer {
			vertical-align: baseline;
		}

		.odometer.odometer-auto-theme .odometer-digit * {
			width: 1ch;
		}" );
	}
} );

/**
 * Register block and enqueue styles and scripts.
 */
add_action( 'init', function() {
	// automatically load dependencies and version
	$asset_file = include(LIBS_DIRECTORY . '/odometer/build/format.asset.php');

	wp_register_script(
		'odometer-be-js',
		LIBS_DIRECTORY_URI . '/odometer/build/format.js',
		$asset_file['dependencies'],
		$asset_file['version']
	);

	wp_set_script_translations( 'odometer-be-js', 'odometer', LIBS_DIRECTORY_URI . '/odometer/languages' );

	register_block_type( 'odometer/format', array(
		'editor_script' => 'odometer-be-js'
	) );
} );