<?php

/**
 * Checks if user has any of the given $roles.
 * _We can ignore an `AND` relation between 2 or more roles, because user only can have one role._
 *
 * @param string|array $roles
 * @param null|int|WP_User $user
 *
 * @return bool
 */
function user_has_role( $roles, $user = null ) {
	if ( !is_array($roles) ) $roles = array( $roles );

	if ( ! $user ) $user = wp_get_current_user();
	elseif ( is_numeric( $user ) ) $user = get_userdata( $user );

	if ( !empty($user->roles) && array_intersect( $roles, (array) $user->roles ) ) return true;

	return false;
}

/**
 * Grant editor more capabilities.
 */
add_action( 'admin_menu', function() {
	if ( !user_has_role( 'editor' ) ) return;

	// grant capabilities if they don't have it
	foreach ( ['edit_theme_options', 'manage_privacy_options', 'manage_options'] as $cap ) {
		if ( !current_user_can( $cap ) ) {
			if ( !isset($role)  ) $role = get_role( 'editor' );
			$role->add_cap( $cap );
		}
	}

	// hide pages
	// ... but they are still accessible if user knows the URL
	remove_menu_page( 'tools.php' );
	remove_submenu_page( 'themes.php', 'themes.php' );
	remove_submenu_page( 'themes.php', 'site-editor.php?path=/patterns' );

	remove_submenu_page( 'themes.php', wptexturize( add_query_arg( [
		'return' => urlencode( $_SERVER['REQUEST_URI'] )
	], 'customize.php' ) ) );

	remove_submenu_page( 'themes.php', wptexturize( add_query_arg( [
		'return' => urlencode( $_SERVER['REQUEST_URI'] ),
		urlencode( 'autofocus[control]' ) => 'header_image'
	], 'customize.php' ) ) );

	remove_submenu_page( 'themes.php', wptexturize( add_query_arg( [
		'return' => urlencode( $_SERVER['REQUEST_URI'] ),
		urlencode( 'autofocus[control]' ) => 'background_image'
	], 'customize.php' ) ) );
} );

/**
 * Remove "User" from "New" in admin menu.
 *
 * @since 1.20.0
 */
add_action( 'wp_before_admin_bar_render', function() {
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu( 'new-user' );
} );
