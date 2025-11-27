<?php

define( 'TABLESORT_DIRECTORY', dirname( __FILE__ ) );
define( 'TABLESORT_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/js/libs/tablesort' );
define( 'TABLESORT_VERSION', '5.6.0' );

/**
 * Register tablesort and it's sorts.
 * To enqueue sorts add "tablesort-{PLUGIN_NAME}" to _your_ dependencies.
 */
add_action( 'wp_enqueue_scripts', function() {
	wp_register_script( 'tablesort', TABLESORT_DIRECTORY_URI . '/module/tablesort.js', [], TABLESORT_VERSION, true );

	$sorts = [
		'date',
		'dotstep',
		'filesize',
		'monthname',
		'number'
	];

	foreach ( $sorts as $sort ) {
		wp_register_script( "tablesort-$sort", TABLESORT_DIRECTORY_URI . "/module/sorts/tablesort.$sort.js", ['tablesort'], TABLESORT_VERSION, true );
	}

	wp_register_script( 'tablesort-all', false, array_map( function( $sort ) {
		return "tablesort-$sort";
	}, $sorts ), TABLESORT_VERSION, true );
} );
