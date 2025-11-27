<?php

add_filter( 'init', function() {

	/**
	 * Get all taxonomies which aren't `publicly_queryable`
	 * and strip links.
	 */
	foreach ( get_taxonomies( ['publicly_queryable' => false] ) as $taxonomy ) {
		// ignore WP taxonomies
		if ( in_array( $taxonomy, ['nav_menu', 'link_category', 'wp_theme', 'wp_template_part_area', 'wp_pattern_category'] ) )
			continue;

		// eventually strip links
		add_filter( "term_links-$taxonomy", function( $links ) {
			return array_map( 'strip_tags', $links, ['a'] );
		} );
	}

}, 11 );
