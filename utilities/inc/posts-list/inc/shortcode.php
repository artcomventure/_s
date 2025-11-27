<?php

/**
 * Get posts list.
 *
 * @param array|WP_REST_Request $atts
 *
 * @return false|string|WP_Error|WP_HTTP_Response|WP_REST_Response
 */
add_shortcode( 'posts-list', 'get_posts_list' );
function get_posts_list( $attr = [] ) {
	$atts = shortcode_atts( [
		'post_type'      => $attr['type'] ?? 'post',
		'post__in'       => [],
		'posts_per_page' => $attr['limit'] ?? get_option( 'posts_per_page' ),
		'orderby' => '',

		'filter' => [],
		'theme'  => 'grid',
		'more'   => '',

		'lang' => function_exists( 'pll_current_language' )
			? pll_current_language() : get_locale(),
	], $attr );

	// make array
	if ( is_string( $atts['post__in'] ) ) {
		$atts['post__in'] = array_filter( explode( ',', $atts['post__in'] ) );
	}

	// build query
	$query = array_filter( $atts, function ( $value, $key ) {
		return $value && ! in_array( $key, [ 'more', 'filter', 'group_by_month', 'theme' ] );
	}, ARRAY_FILTER_USE_BOTH );

	// order by post__in
	if ( $atts['post__in'] ) {
		if ( ($query['orderby'] ?? '') == 'menu_order' )
			$query['orderby'] = 'post__in';
	} else {
		// remove current post from query
		if ( get_post_type() == $atts['post_type'] )
			$query['post__not_in'] = [get_the_ID()];

		if ( isset($query['orderby']) ) {
			if ( $query['orderby'] !== 'date' ) $query['order'] = 'ASC';
			if ( $query['orderby'] === 'menu_order' ) $query['orderby'] .= ' title';
		}

		// parse filter (set from Gutenberg block)
		if ( is_string( $filter = $atts['filter'] ) ) {
			try {
				$filter         = urldecode( $filter );
				$filter         = (array) json_decode( $filter );
				$atts['filter'] = $filter;
			} catch ( Exception ) {}
		}

		// build `tax_query`
		$query += [ 'tax_query' => [] ];
		if ( is_array( $atts['filter'] ) ) {
			foreach ( $atts['filter'] as $taxonomy => $operators ) {
				foreach ( $operators as $operator => $terms ) {
					$query['tax_query'][] = [
						'taxonomy' => $taxonomy,
//                    'field' => 'term_id',
						'terms'    => $terms,
						'operator' => $operator
					];
				}
			}
		}
	}

	// set page to get right pool of posts
	$page           = get_query_var( 'paged' ) ?: 1;
	$query['paged'] = $page;

	// query posts
	$posts = new WP_Query( $query );

	if ( $posts->have_posts() ) {
		// custom theme
		if ( $atts['theme'] !== 'grid' )
			$output = apply_filters( "posts-list-theme-{$atts['theme']}", '', $posts, $atts );

		if ( empty($output) ) { // default grid theme
			ob_start(); ?>

			<?php while ( $posts->have_posts() ) : $posts->the_post();
				get_template_part( 'template-parts/content', get_post_type(), $atts );
			endwhile;

			// restore original post data
			wp_reset_postdata();

			$output = ob_get_clean();
		}

		if ( $atts['more'] && $page < $posts->max_num_pages ) : ob_start(); ?>
            <button class="wp-element-button load-more no-pjax"
                    aria-label="<?php _e( 'Load next results' ) ?>"
                    data-next-page="<?php echo (int) $page + 1 ?>">
				<?php echo $atts['more'] ?>
            </button>
			<?php $output .= ob_get_clean();
		endif;
	}

	$output = ( $output ?? '' )
		?: '<div class="no-entries-message">' . wpautop( __( 'No entries were found.', 'posts-list' ) ) . '</div>';

	ob_start();
	do_action( 'posts-list-prepend', $posts, $atts );
	$output = ob_get_clean() . $output;

	ob_start();
	do_action( 'posts-list-append', $posts, $atts );
	$output .= ob_get_clean();

	return '<span class="posts-list--do-not-append" data-result-cnt="' . $posts->found_posts . '" hidden><span>'
	       . sprintf( _n( '%d result', '%d results', $posts->found_posts, 'posts-list' ), $posts->found_posts )
	       . '</span></span>'
	       . $output;
}
