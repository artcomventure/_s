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

// remove AI image generation buttons
// @since 1.20.2
add_filter( 'aioseo_ai_image_generator_extend_featured_image_button', '__return_false' );
add_filter( 'aioseo_ai_image_generator_extend_image_block_toolbar', '__return_false' );
add_filter( 'aioseo_ai_image_generator_extend_image_block_placeholder', '__return_false' );

// remove admin menu item
add_action( 'wp_before_admin_bar_render', function() {
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu( 'aioseo-main' );
} );

// asynchronously load aioseo CSS
// @since 1.20.2
add_action( 'wp_enqueue_scripts', function() {
	foreach ( wp_styles()->registered as $file ) {
		if ( $file->handle !== 'aioseo/css/src/vue/standalone/blocks/table-of-contents/global.scss' ) continue;
		if ( ($file->args ?: 'all') === 'all' ) $file->args = 'async';
		break;
	}
}, 1982 );
