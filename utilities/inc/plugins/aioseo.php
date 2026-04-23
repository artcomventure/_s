<?php

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( !is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' )
     && !is_plugin_active( 'all-in-one-seo-pack-pro/all_in_one_seo_pack.php' ) ) return;

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

// remove AI
// @since 1.20.2
add_filter( 'aioseo_ai_image_generator_extend_featured_image_button', '__return_false' );
add_filter( 'aioseo_ai_image_generator_extend_image_block_toolbar', '__return_false' );
add_filter( 'aioseo_ai_image_generator_extend_image_block_placeholder', '__return_false' );
// @since 1.20.4
add_filter( 'aioseo_ai_assistant_extend_block_editor_inserter_button', '__return_false' );
add_filter( 'aioseo_ai_assistant_extend_paragraph_placeholder', '__return_false' );

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

/**
 * Change WP's url to redirect's target url.
 * ... in case SPA is enabled.
 *
 * @since 1.20.5
 */
add_filter( 'post_type_link', function( $url ) {
	if ( get_option( 'pjax' ) && isset( aioseo()->redirects ) ) {
		$path = wp_make_link_relative( $url );

		if ( $redirect = aioseo()->redirects->redirect->getRedirects( $path ) ) {
			$target = $redirect[0]->target_url;
			$url = str_starts_with( $target, 'http' ) ? $target : home_url( $target );
		}
	}

	return $url;
} );
