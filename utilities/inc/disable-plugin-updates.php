<?php

/**
 * Since custom plugins could have the same naming as plugins listed on https://wordpress.org/plugins/
 * we remove any update notification caused by these _name overlaps_.
 *
 * In most cases the plugins remove their notification by themselves
 * ... but that only takes effect once the plugin is activated.
 */

// remove unwanted bulk action
add_filter( 'bulk_actions-plugins', function( $bulk_actions ) {
	unset( $bulk_actions['enable-auto-update-selected'], $bulk_actions['disable-auto-update-selected'] );

	if ( !isDev() ) {
		unset( $bulk_actions['update-selected'], $bulk_actions['delete-selected'] );
	}

	return $bulk_actions;
} );

//add_filter( 'plugin_auto_update_setting_html', function( $html, $plugin_file, $plugin_data ) {
//	$id = explode( '/', $plugin_file );
//	$id = current( $id );
//
//	$url = wp_nonce_url( 'plugins.php?action=toggle-updates&plugin=' . $plugin_file, 'toggle-updates' );
//
//	$input = '<input id="disable-updates-' . $id . '" class="on-off" type="checkbox" onchange="console.log(\'' . $url . '\')" />';
//
//	$label = '<label for="disable-updates-' . $id . '">';
//	$label .= __( 'Disable updates', 'utilities' );
//	$label .= '</label>';
//
//	return $input . $label . $html;
//}, 10, 3 );

add_filter( 'site_transient_update_plugins', function( $value ) {
    foreach ( array(
        'slider/slider.php',
    ) as $plugin_file ) {
        if ( isset( $value->response[$plugin_file] ) ) {
            unset( $value->response[$plugin_file] );
        }
    }

    return $value;
} );