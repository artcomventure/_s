<?php

define( 'TEASERLIST_DIRECTORY', dirname( __FILE__ ) );
define( 'TEASERLIST_DIRECTORY_URI', GUTENBERG_DIRECTORY_URI . '/inc/teaser-list' );

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_script( 'teaser-list', TEASERLIST_DIRECTORY_URI . '/app.js', ['behaviours'], false, true );
} );

/**
 * Js endpoint for `get_teaser_list()`.
 */
add_action( 'rest_api_init', function() {
	register_rest_route( 'teaser-list/v1', '/query', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'get_teaser_list',
		'permission_callback' => '__return_true',
	) );
} );

/**
 * Get teaser list.
 *
 * @param array|WP_REST_Request $atts
 *
 * @return false|string|WP_Error|WP_HTTP_Response|WP_REST_Response
 */
add_shortcode( 'teaser-list', 'get_teaser_list' );
function get_teaser_list( $attr = [] ) {
	if ( $is_rest_request = $attr instanceof WP_REST_Request )
        $attr = $attr->get_params();

	$atts = shortcode_atts( [
		'post_type' => 'post',
		'posts_per_page' => get_option( 'posts_per_page' ),
		'orderby' => 'date',
        'offset' => 0,
		'cat' => '', // post category only
        'tax_query' => [],

		'more' => ''
	], $attr );

	$query = array_filter( $atts, function( $value, $key ) {
		return $value && !in_array( $key, ['more'] );
	}, ARRAY_FILTER_USE_BOTH );

	$posts = new WP_Query( $query );

	$query['offset'] = $query['offset'] ?? 0; // from here offset is mandatory
    $has_more = $posts->found_posts > $query['offset'] + $posts->post_count;
    $query['offset'] += $posts->post_count; // adjust offset

	ob_start();

	while ( $posts->have_posts() ) : $posts->the_post();
		get_template_part( 'template-parts/content', get_post_type(), $atts );
	endwhile;

	if ( $atts['more'] && $posts->found_posts > $query['posts_per_page'] ) : ?>
		<button class="wp-element-button aligncenter load-more no-pjax" data-query='<?php echo json_encode( $query ) ?>'>
			<?php echo $atts['more'] ?>
		</button>
	<?php endif;

	// restore original post data
	wp_reset_postdata();

    $output = ob_get_clean();

	if ( $is_rest_request ) return rest_ensure_response( [
		'status' => 'success',
        'query' => $query,
		'has_more' => $has_more,
		'html' => $output,
	] );


	return $output;
}

// auto include files
auto_include_files( TEASERLIST_DIRECTORY . '/inc' );
