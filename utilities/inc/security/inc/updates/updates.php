<?php

/**
 * Dashboard's security widget entry.
 */
add_filter( 'security_checks', function( $checks ) {
	$status = (function() {
		if ( ($core = get_preferred_from_update_core()) && $core->response === 'upgrade' ) {
			return false;
		}

		$plugins = get_site_transient( 'update_plugins' );
		$themes = get_site_transient( 'update_themes' );
		if ( !empty( $plugins->response ) || !empty( $themes->response ) || wp_get_translation_updates() ) {
			return 'warning';
		}

		return true;
	})();

	$checks['update'] = [
		'title' => $status === true ? __( 'No updates available', 'security' ) : __( 'Updates available', 'security' ),
		'status' => $status,
		'info' => (function() use ( $status ) {
			$info = __( "There is a new version of WordPress available.", 'security' );

			if ( $status === true ) {
				$info = __( 'There are no updates available.', 'security' );
			}
			else {
				if ( $status == 'warning' ) {
					$info = __( "Updates available.", 'security' );
				}

				$info .= ' ';

				if ( wp_is_file_mod_allowed( 'capability_update_core' ) ) {
					$info .= '<a href="' . admin_url( 'update-core.php' ) . '">' . __( "Please immediately run updates", 'security' ) . '</a>.';
				}
				else $info .= __( "Please notify the site administrator!", 'security' );
			}

			return $info;
		})(),
		'description' => sprintf(
			__( 'Many updates include security fixes. It is therefore important to keep WordPress itself and all plugins always up to date.%s', 'security' ),
			!wp_is_file_mod_allowed( 'capability_update_core' ) ? '<b>' . __( 'These actions are only possible in the development environment!', 'security' ) . '</b>' : ''
		)
	];

	return $checks;
} );

/**
 * Since custom plugins could have the same naming as plugins listed on https://wordpress.org/plugins/
 * we remove any update notification caused by these _name overlaps_.
 *
 * In most cases the plugins remove their notification by themselves
 * ... but that only takes effect once the plugin is activated.
 */

add_action( 'admin_enqueue_scripts', function() {
	global $pagenow;

	if ( $pagenow != 'plugins.php' ) return;

	wp_enqueue_script( 'utilities-updates', SECURITY_DIRECTORY_URI . '/inc/updates/app.js', [], false, true );
	wp_enqueue_style( 'utilities-updates', SECURITY_DIRECTORY_URI . '/inc/updates/style.css' );
} );

/**
 * Remove unwanted bulk action.
 */
add_filter( 'bulk_actions-plugins', function( $bulk_actions ) {
	unset( $bulk_actions['enable-auto-update-selected'], $bulk_actions['disable-auto-update-selected'] );

	if ( !isLocal() ) {
		unset( $bulk_actions['update-selected'], $bulk_actions['delete-selected'] );
	}

	return $bulk_actions;
} );

/**
 * Add custom "disable updates" action.
 */
add_filter( 'plugin_auto_update_setting_html', function( $html, $plugin_file, $plugin_data ) {
	$id = explode( '/', $plugin_file );
	$id = current( $id );

	$url = wp_nonce_url( add_query_arg( [
		'action' => 'toggle-updates',
		'checked[]' => $plugin_file
	], admin_url( 'plugins.php' ) ), 'bulk-plugins' );

	$disable_updates = (array) get_site_option( 'disable_update_plugins', [] );

	$input = '<input id="disable-updates-' . $id . '" class="on-off"' . (in_array($plugin_file, $disable_updates) ? ' checked=""' : '') . ' type="checkbox" value="' . $url . '" />';

	$label = '<label for="disable-updates-' . $id . '">';
	$label .= __( 'Disable updates', 'security' );
	$label .= '</label>';

	return $input . $label; // . $html;
}, 10, 3 );

/**
 * Custom bulk "en-/disable updates" action.
 */
add_filter( 'handle_bulk_actions-plugins', function( $sendback, $action, $plugins ) {
	if ( $action == 'toggle-updates' ) {
		$disable_updates = (array) get_site_option( 'disable_update_plugins', [] );

		foreach ( $plugins as $plugin ) {
			if ( ($key = array_search( $plugin, $disable_updates )) !== false ) {
				unset( $disable_updates[$key] );
			}
			else $disable_updates[] = $plugin;
		}

		$disable_updates = array_unique( $disable_updates );
		$all_items = apply_filters( 'all_plugins', get_plugins() );
		$disable_updates = array_intersect( $disable_updates, array_keys( $all_items ) );

		update_site_option( 'disable_update_plugins', $disable_updates );
	}

	return $sendback;
}, 10, 3 );

/**
 * Eventually remove plugins update notification (BE and CLI).
 */
add_filter( 'site_transient_update_plugins', function( $value ) {
	if ( !wp_is_file_mod_allowed( 'capability_update_core' ) ) $value->response = [];
	else foreach ( (array) get_site_option( 'disable_update_plugins', [] ) as $plugin_file ) {
		unset( $value->response[$plugin_file] );
	}

	return $value;
} );

/**
 * Eventually remove themes update notification (BE and CLI).
 */
add_filter( 'site_transient_update_themes', function( $value ) {
	if ( !wp_is_file_mod_allowed( 'capability_update_core' ) ) $value->response = [];
	return $value;
} );

/**
 * Change plugin table's update column caption.
 */
add_filter( 'manage_plugins_columns',  function( $columns ) {
	if ( isset( $columns['auto-updates'] ) ) {
		$columns['auto-updates'] = __( 'Updates', 'security' );
	}

	return $columns;
} );
