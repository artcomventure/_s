<?php /**
 * Change post navigation order from `post_date` to `menu_order`.
 *
 * ```
 * add_filter( 'do-post-navigation-by-menu_order', function( $post_types ) {
 *     $post_types[] = 'POST_TYPE_NAME';
 *     return $post_types;
 * } );
 * ```
 */

class PostNavigationByMenuOrder {

	// register filters
	public function __construct() {
		add_filter( 'get_previous_post_where', [$this, 'set_post_navigation_where'], 10, 5 );
		add_filter( 'get_next_post_where', [$this, 'set_post_navigation_where'], 10, 5 );

		add_filter( 'get_previous_post_sort', [$this, 'set_post_navigation_sort'], 10, 2 );
		add_filter( 'get_next_post_sort', [$this, 'set_post_navigation_sort'], 10, 2 );
	}

	// check if post navigation for _current_ post type should be changed
	public function doPostNavigationByMenuOrder( int|WP_Post $post = 0 ) {
		return in_array( get_post_type( $post ), apply_filters( 'do-post-navigation-by-menu_order', [] ) );
	}

	// adjust WHERE clause
	public function set_post_navigation_where( $where, $in_same_term, $excluded_terms, $taxonomy, $post ) {
		if ( $this->doPostNavigationByMenuOrder( $post ) ) {
			global $wpdb;

			// both filters use this very same function
			$compare = current_filter() == 'get_previous_post_where' ? '<' : '>';

			$where = preg_replace('/p\.post_date\s*[<>]=?\s*\'[^\']+\'\s*(AND)?/i', '', $where );

			$current_order = $post->menu_order;
			$current_title = $post->post_title;

			$where .= $wpdb->prepare(
				" AND (p.menu_order $compare %d OR (p.menu_order = %d AND p.post_title $compare %s))",
				$current_order,
				$current_order,
				$current_title
			);
		}

		return $where;
	}

	// adjust sort
	public function set_post_navigation_sort( $sort, $post ) {
		if ( $this->doPostNavigationByMenuOrder( $post ) ) {
			// both filters use this very same function
			$dir = current_filter() == 'get_previous_post_sort' ? 'DESC' : 'ASC';
			$sort = "ORDER BY p.menu_order $dir, p.post_title $dir LIMIT 1";
		}

		return $sort;
	}
}

new PostNavigationByMenuOrder();