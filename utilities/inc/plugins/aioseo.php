<?php

add_filter( 'aioseo_breadcrumbs_trail', function( $breadcrumbs ) {
	foreach ( $breadcrumbs as $i => $link ) {
		// remove link from not queryable taxonomies
		if ( $link['type'] == 'taxonomy' && !is_taxonomy_viewable( $link['reference'] ) ) {
			$breadcrumbs[$i]['link'] = '';
		}
	}

	return $breadcrumbs;
} );
