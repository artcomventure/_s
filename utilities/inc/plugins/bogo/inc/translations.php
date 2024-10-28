<?php

add_filter( 'option_date_format', 'translate_date_format' );
function translate_date_format( $value ) {
	return bogo_translate( 'date_format', 'date_format', $value );
}

add_filter( 'option_time_format', 'translate_time_format' );
function translate_time_format( $value ) {
	return bogo_translate( 'time_format', 'time_format', $value );
}

add_filter( 'bogo_terms_translation', function( $items, $locale ) {
	remove_filter( 'option_date_format', 'translate_date_format' );
	remove_filter( 'option_time_format', 'translate_time_format' );

	$items[] = array(
		'name' => 'date_format',
		'original' => $date_format = get_option( 'date_format' ),
		'translated' => bogo_translate(
			'date_format', 'date_format', $date_format
		),
		'context' => __( 'Date Format' ),
		'cap' => 'manage_options',
	);

	$items[] = array(
		'name' => 'time_format',
		'original' => $time_format = get_option( 'time_format' ),
		'translated' => bogo_translate(
			'time_format', 'time_format', $time_format
		),
		'context' => __( 'Time Format' ),
		'cap' => 'manage_options',
	);

	add_filter( 'option_date_format', 'translate_date_format' );
	add_filter( 'option_time_format', 'translate_time_format' );

	return $items;
}, 10,2 );
