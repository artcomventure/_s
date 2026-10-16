<?php

/**
 * Checks if user has any of the given $roles.
 * _We can ignore an `AND` relation between 2 or more roles, because user only can have one role._
 *
 * @param array|string $roles
 * @param WP_User|int|null $user
 *
 * @return bool
 */
function user_has_role( array|string $roles, WP_User|int $user = null ): bool {
	if ( !is_array($roles) ) $roles = array( $roles );

	if ( ! $user ) $user = wp_get_current_user();
	elseif ( is_numeric( $user ) ) $user = get_userdata( $user );

	if ( !empty($user->roles) && array_intersect( $roles, $user->roles ) ) return true;

	return false;
}

/**
 * Grant editors widgets, menus and customizer while this theme is active.
 * Must be persisted: the customizer checks caps on `setup_theme`, before the theme is loaded.
 */
add_action( 'admin_init', function() {
	if ( ( $role = get_role( 'editor' ) ) && empty( $role->capabilities['edit_theme_options'] ) ) {
		$role->add_cap( 'edit_theme_options' );
	}
} );

add_action( 'switch_theme', function() {
	get_role( 'editor' )?->remove_cap( 'edit_theme_options' );
} );

/**
 * Grant editors the privacy page.
 * Core maps `manage_privacy_options` to `manage_options`.
 */
add_filter( 'map_meta_cap', function( $caps, $cap, $user_id ) {
	if ( $cap === 'manage_privacy_options' && user_has_role( 'editor', $user_id ) ) {
		return ['edit_others_pages'];
	}

	return $caps;
}, 10, 3 );

/**
 * Limit the customizer for editors.
 * Controls of other sections are removed and their settings locked (also blocks saving).
 */
add_action( 'customize_register', function( $wp_customize ) {
	if ( !user_has_role( 'editor' ) ) return;

	$sections = ['title_tagline', 'pages_for'];
	$panels = ['nav_menus', 'widgets'];

	$keep = $lock = [];
	foreach ( $wp_customize->controls() as $control ) {
		$section = $wp_customize->get_section( $control->section );
		$allowed = in_array( $control->section, $sections, true ) || ( $section && in_array( $section->panel, $panels, true ) );

		foreach ( $control->settings as $setting ) {
			if ( $allowed ) $keep[$setting->id] = $setting;
			else $lock[$setting->id] = $setting;
		}

		if ( !$allowed ) $wp_customize->remove_control( $control->id );
	}

	// e.g. `blogname` requires `manage_options`
	foreach ( $keep as $setting ) $setting->capability = 'edit_theme_options';

	// settings shared with an allowed control stay editable
	foreach ( array_diff_key( $lock, $keep ) as $setting ) $setting->capability = 'do_not_allow';
}, 1000 );

/**
 * Remove caps persisted to the editor role by versions <= 1.20.12.
 */
add_action( 'admin_init', function() {
	if ( !$role = get_role( 'editor' ) ) return;

	foreach ( ['manage_options', 'manage_privacy_options'] as $cap ) {
		if ( !empty( $role->capabilities[$cap] ) ) $role->remove_cap( $cap );
	}
} );

/**
 * Block pages for editors.
 */
add_action( 'admin_init', function() {
	global $pagenow;
	if ( !user_has_role( 'editor' ) ) return;

	// "Design" itself links to themes.php
	if ( $pagenow === 'themes.php' ) {
		wp_safe_redirect( admin_url( 'widgets.php' ) );
		exit;
	}

	if ( in_array( $pagenow, ['tools.php', 'font-library.php'], true ) ) {
		wp_die( __( 'Sorry, you are not allowed to access this page.' ), 403 );
	}
} );

/**
 * Hide pages for editors.
 */
add_action( 'admin_menu', function() {
	if ( !user_has_role( 'editor' ) ) return;

	remove_menu_page( 'tools.php' );
	remove_submenu_page( 'themes.php', 'themes.php' );
	remove_submenu_page( 'themes.php', 'font-library.php' );

	// customizer deep links (header, background), sections are removed for editors
	global $submenu;
	foreach ( $submenu['themes.php'] ?? [] as $id => $item ) {
		if ( str_starts_with( $item[2], 'customize.php' ) && str_contains( $item[2], 'autofocus' ) ) unset( $submenu['themes.php'][$id] );
	}

	// core re-parents "Settings" to the only accessible sub page (privacy)
	// but keeps the `manage_options` cap and then drops the whole menu
	global $menu;
	foreach ( $menu as $id => $item ) {
		if ( $item[2] === 'options-privacy.php' ) $menu[$id][1] = 'manage_privacy_options';
	}
}, 999 );

/**
 * Remove "User" from "New" in admin menu.
 *
 * @since 1.20.0
 */
add_action( 'wp_before_admin_bar_render', function() {
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu( 'new-user' );
} );
