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

	// grant the edit_theme_options capability if they don't have it
	if ( !current_user_can( 'edit_theme_options' ) ) {
		$role = get_role( 'editor' );
		$role->add_cap( 'edit_theme_options' );
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
