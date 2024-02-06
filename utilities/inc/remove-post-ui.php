<?php

// disable post UI
add_action( 'customize_register', function( $wp_customize ) {
	$wp_customize->add_setting( 'disable_posts', array(
		'type' => 'option',
		'capability' => 'manage_options',
	) );

	$wp_customize->add_control( 'disable_posts', array(
		'label' => __( 'Disable Posts', 'utilities' ),
		'description' => __( 'Remove post UI from backend. Post menu page will be removed once there are no posts anymore.', 'utilities' ),
		'section' => 'utilities',
		'type' => 'checkbox'
	) );
} );

/**
 * It isn't allowed to unregister internal post types (`unregister_post_type( 'post' );`).
 * So instead of messing with _globals_ we remove all traces from backend.
 * Requirement: there is no post created.
 */

// remove 'New Post' link from admin bar
add_action( 'admin_bar_menu', function() {
	if ( !get_option( 'disable_posts' ) ) return;

	global $wp_admin_bar;
	$wp_admin_bar->remove_node( 'new-post' );
}, 999 );

// remove admin menu entry
add_action( 'admin_menu', function() {
	if ( !get_option( 'disable_posts' ) ) return;

	remove_submenu_page('edit.php', 'post-new.php');

	if ( count(get_posts( array( 'post_status' => 'publish,pending,draft,future,private,trash' ) )) ) return;

	// remove taxonomies
	remove_submenu_page('edit.php', 'edit-tags.php?taxonomy=category');
	remove_submenu_page('edit.php', 'edit-tags.php?taxonomy=post_tag');

	remove_menu_page( 'edit.php' );
}, 999 );

// redirect to dashboard
add_action( 'admin_init', function() {
	if ( !get_option( 'disable_posts' ) ) return;

	global $pagenow;

	// forbid post's post-new.php
	if ( $pagenow == 'post-new.php' && (!isset($_GET['post_type']) || $_GET['post_type'] == 'post') ) {
		$forbidden = true;
	}
	elseif ( !count(get_posts( array( 'post_status' => 'publish,pending,draft,future,private,trash' ) ) ) ) {
		// posts overview
		if ( $pagenow == 'edit.php' && (!isset($_GET['post_type']) || $_GET['post_type'] == 'post') ) {
			$forbidden = true;
		}
		// forbid post's edit.php
		elseif ( $pagenow == 'post.php' && get_post_type($_GET['post'] ) == 'post' ) {
			$forbidden = true;
		}
		// forbid post taxonomies
		elseif ( $pagenow == 'edit-tags.php' && in_array( $_GET['taxonomy'], array( 'category', 'post_tag' ) ) ) {
			$forbidden = true;
		}
	}

	if ( !empty($forbidden) ) {
		wp_redirect( admin_url(), 302 );
		exit;
	}
} );
