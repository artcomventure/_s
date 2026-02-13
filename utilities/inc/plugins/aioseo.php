<?php

add_filter( 'aioseo_breadcrumbs_trail', function( $breadcrumbs ) {
	foreach ( $breadcrumbs as $i => $link ) {
		// no `<br />`
		$breadcrumbs[$i]['label'] = preg_replace( '/<br( ?\/)?>/', ' ', $link['label'] );

		// remove link from not queryable taxonomies
		if ( $link['type'] == 'taxonomy' && !is_taxonomy_viewable( $link['reference'] ) ) {
			$breadcrumbs[$i]['link'] = '';
		}
		// remove link on empty pages
		elseif ( $link['type'] == 'page' && !$link['reference']->post_content ) {
			$breadcrumbs[$i]['link'] = '';
		}
	}

	return $breadcrumbs;
} );

// remove admin menu item
add_action( 'wp_before_admin_bar_render', function() {
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu( 'aioseo-main' );
} );
