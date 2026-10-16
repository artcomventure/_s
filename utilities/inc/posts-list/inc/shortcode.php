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
		'empty'   => '',
		'more'   => '',

		'lang' => function_exists( 'pll_current_language' )
			? pll_current_language() : get_locale(),
	], $attr );

	// make array
	if ( is_string( $atts['post__in'] ) ) {
		$atts['post__in'] = explode( ',', $atts['post__in'] );
	}

	// sanitize: attributes are set by authors
	$atts['post__in'] = array_values( array_filter( array_map( 'intval', (array) $atts['post__in'] ), fn( $id ) => $id > 0 ) );

	// public post types only (no `any`, no internal types like `wpcf7_contact_form`)
	$post_types = array_intersect(
		array_map( 'trim', is_array( $atts['post_type'] ) ? $atts['post_type'] : explode( ',', (string) $atts['post_type'] ) ),
		get_post_types( ['public' => true] )
	);
	$atts['post_type'] = count( $post_types ) > 1 ? array_values( $post_types ) : ( reset( $post_types ) ?: 'post' );

	// like WP_Query: -1 = all, invalid/0 = default
	$atts['posts_per_page'] = (int) $atts['posts_per_page'];
	if ( $atts['posts_per_page'] < -1 || !$atts['posts_per_page'] ) $atts['posts_per_page'] = (int) get_option( 'posts_per_page' );

	if ( !in_array( $atts['orderby'], ['', 'date', 'modified', 'title', 'name', 'menu_order', 'post__in', 'rand'], true ) ) {
		$atts['orderby'] = '';
	}

	$atts['theme'] = sanitize_key( $atts['theme'] ) ?: 'grid';
	$atts['lang'] = sanitize_text_field( (string) $atts['lang'] );

	// build query
	$query = array_filter( $atts, function ( $value, $key ) {
		return $value && ! in_array( $key, [ 'empty', 'more', 'filter', 'group_by_month', 'theme' ] );
	}, ARRAY_FILTER_USE_BOTH );

	// order by post__in
	if ( $atts['post__in'] ) {
		if ( ($query['orderby'] ?? '') == 'menu_order' )
			$query['orderby'] = 'post__in';
	} else {
		// remove current post from query
		if ( in_array( get_post_type(), (array) $atts['post_type'], true ) )
			$query['post__not_in'] = [get_the_ID()];

		if ( isset($query['orderby']) ) {
			if ( $query['orderby'] !== 'date' ) $query['order'] = 'ASC';
			if ( $query['orderby'] === 'menu_order' ) $query['orderby'] .= ' title';
		}

		// parse filter (set from Gutenberg block)
		if ( is_string( $atts['filter'] ) ) {
			$atts['filter'] = json_decode( urldecode( $atts['filter'] ), true );
		}

		// build `tax_query`
		$query += [ 'tax_query' => [] ];
		foreach ( is_array( $atts['filter'] ) ? $atts['filter'] : [] as $taxonomy => $operators ) {
			if ( !taxonomy_exists( $taxonomy ) || !is_array( $operators ) ) continue;

			foreach ( $operators as $operator => $terms ) {
				if ( !in_array( $operator, ['IN', 'NOT IN', 'AND', 'EXISTS', 'NOT EXISTS'], true ) ) continue;

				// term ids (`field` defaults to `term_id`)
				$terms = array_values( array_filter( array_map( 'intval', array_filter( (array) $terms, 'is_scalar' ) ), fn( $id ) => $id > 0 ) );

				$query['tax_query'][] = [
					'taxonomy' => $taxonomy,
//                    'field' => 'term_id',
					'terms'    => $terms,
					'operator' => $operator
				];
			}
		}
	}

	// set page to get right pool of posts
	// static front page: WP moves `paged` to `page`
	$page           = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	$query['paged'] = $page;

	// query posts
	$posts = new WP_Query( [
        'is_posts_list_query' => true,
        'posts_list_theme' => $atts['theme'],
    ] + $query );

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
				<?php echo wp_kses_post( $atts['more'] ) ?>
            </button>
			<?php $output .= ob_get_clean();
		endif;
	}

	$output = ( $output ?? '' )
		?: '<div class="no-entries-message">' . wpautop( wp_kses_post( $atts['empty'] ) ?: __( 'No entries were found.', 'posts-list' ) ) . '</div>';

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
