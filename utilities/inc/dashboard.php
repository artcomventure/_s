<?php

// clean up dashboard
add_action( 'wp_dashboard_setup', function() {
	remove_action('welcome_panel', 'wp_welcome_panel');
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' ); // events and news
	remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
	if ( function_exists( 'user_has_role' ) && !user_has_role( 'administrator' ) )
		remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' );
} );
