<?php

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( !is_plugin_active( 'bogo/bogo.php' ) ) return;

// no flags in language switcher
add_filter( 'bogo_use_flags', '__return_false' );

// show locale instead of native names
add_filter( 'bogo_language_switcher_links', function( $links, $args ) {
	foreach ( $links as &$link ) {
		$link['native_name'] = explode( '_', $link['locale'] );
		$link['native_name'] = strtoupper( $link['native_name'][0] );
	}

	// default locale on front
	$default_locale = bogo_get_default_locale();
	usort( $links, function( $a, $b ) use ( $default_locale ) {
		return $b['locale'] == $default_locale ? 1 : -1;
	} );

	return $links;
}, 10, 2 );

// maybe unserialize serialized metadata
// otherwise they will be serialized again
add_filter( 'bogo_duplicate_post', function( $postarr, $original_post, $locale ) {
	if ( $postarr['meta_input'] ?? false ) {
		foreach ( $postarr['meta_input'] as $meta_key => $values ) {
			foreach ( $values as $key => $value ) {
				$postarr['meta_input'][$meta_key][$key] = maybe_unserialize( $value );
			}
		}
	}

	return $postarr;
}, 10, 3 );

// auto include /inc files
auto_include_files( dirname( __FILE__ ) . '/inc' );