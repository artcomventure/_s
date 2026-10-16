<?php

/**
 * Enqueue backend (dashboard) scripts and styles.
 */
add_action( 'admin_enqueue_scripts', function( $pagenow ) {
	if ( $pagenow != 'index.php' ) return;

	wp_enqueue_style('posts-list-posts-order', UTLTS_POSTSLIST_DIRECTORY_URI.'/inc/taxonomy/inc/dashboard/style.css' );
	wp_enqueue_script('list-events-order', UTLTS_POSTSLIST_DIRECTORY_URI.'/inc/taxonomy/inc/dashboard/app.js', ['wp-i18n', 'jquery-ui-autocomplete', 'jquery-ui-sortable'], false, true );
} );

/**
 * Register dashboard widget for ordering list events.
 */
add_action( 'wp_dashboard_setup', function() {

	if ( $post_types = apply_filters( 'register_list_taxonomy', [] ) ) {
	    wp_add_dashboard_widget("list-posts-order-widget", __( 'Sort lists', 'posts-list' ), function() use ( $post_types ) { ?>
            <form action="<?php echo admin_url( 'admin-post.php?action=list-posts-order' ) ?>" method="post" data-ajax-nonce="<?php echo esc_attr( wp_create_nonce( 'add-post-to-list' ) ) ?>">
	            <?php wp_nonce_field( 'list-posts-order_save', 'list-posts-order_nonce' ); ?>

                <div id="switch-list-post-type-wrapper">
                    <label for="switch-list-post-type"><?php _e( 'Post type', 'posts-list' ) ?>:</label>
                    <select id="switch-list-post-type"<?php echo count($post_types) < 2 ? ' disabled' : '' ?>>
                        <?php foreach ( $post_types as $post_type ) :
                            $labels = get_post_type_labels( get_post_type_object( $post_type ) ); ?>
                            <option value="<?php echo esc_attr( $post_type ) ?>"><?php echo esc_html( $labels->name ) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php foreach ( $post_types as $post_type ) :
	                $labels = get_post_type_labels( get_post_type_object( $post_type ) ); ?>
	                <div class="list-post-type-wrapper" data-post-type="<?php echo esc_attr( $post_type ) ?>">
                        <?php if ( !$lists = get_terms( [
                            'taxonomy' => "{$post_type}_list",
                            'hide_empty' => false
                        ] ) ) {
                            echo '<p class="description">' . sprintf(
                                __( 'There is no list defined yet. Please <a href="%s">add list</a> first.', 'posts-list' ),
                                esc_url( add_query_arg( ['post_type' => $post_type, 'taxonomy' => "{$post_type}_list"], admin_url( 'edit-tags.php' ) ) )
                            ) . '</p>';
                        } else { ?>

                            <div class="switch-list-wrapper">
                                <label for="switch-<?php echo esc_attr( $post_type ) ?>-list"><?php _e( 'List to edit', 'posts-list' ) ?>:</label>
                                <select id="switch-<?php echo esc_attr( $post_type ) ?>-list"<?php echo count($lists) < 2 ? ' disabled' : '' ?>>
                                    <?php foreach ( $lists as $list ) : ?>
                                        <option value="<?php echo (int) $list->term_id ?>"><?php echo esc_html( $list->name ) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <?php foreach ( $lists as $list ) : ?>
                                <div class="posts-list" data-list="<?php echo (int) $list->term_id ?>">
                                    <p class="description"><?php printf( __( 'There are no %s in the list %s yet.', 'posts-list' ), esc_html( $labels->name ), '<i>' . esc_html( $list->name ) . '</i>' ) ?></p>
                                    <?php foreach ( get_list_posts( $list ) as $post ) {
                                        list_post_autocomplete_html( $post, $list );
                                    } ?>
                                    <button class="button-secondary"><?php _e( 'Add' ) ?></button>
                                    <button type="submit" class="button-primary"><?php _e( 'Save lists', 'posts-list' ) ?></button>
                                </div>
                            <?php endforeach;
                        } ?>
                    </div>
                <?php endforeach; ?>
            </form>
        <?php } );
	}
} );

add_action( 'wp_ajax_add-post-to-list', function() {
	check_ajax_referer( 'add-post-to-list', 'nonce' );

	$list = absint( wp_unslash( $_GET['list'] ?? 0 ) );
	$term = $list ? get_term_by( 'term_taxonomy_id', $list ) : null;
	$taxonomy = $term ? get_taxonomy( $term->taxonomy ) : null;

	if ( !$taxonomy || !current_user_can( $taxonomy->cap->assign_terms ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do this.' ), 403 );
	}

	list_post_autocomplete_html( null, $list );
	wp_die();
} );
function list_post_autocomplete_html( $post = null, $list = '' ): void {
	echo get_list_post_autocomplete_html( $post, $list ?: absint( wp_unslash( $_GET['list'] ?? 0 ) ) );
}

function get_list_post_autocomplete_html( $post, $list ): string {
	if ( $post  ) {
		if ( !$post = get_post( $post ) ) return '';
	}

    // list is mandatory
    if ( is_numeric($list) && !$list = get_term_by( 'term_taxonomy_id', $list ) ) return '';
    if ( !$list instanceof WP_Term ) return '';

	return '<div class="post">
        <i class="dashicons dashicons-menu"></i>
        <input type="hidden" value="' . esc_attr( $post->ID ?? '' ) . '" name="list-posts-order[' . esc_attr( $list->taxonomy ) . '][' . (int) $list->term_id . '][]" />
        <input type="text" ' . ($post ? 'class="selected"' : '') . ' value="' . esc_attr( get_the_title($post ?? -1) ) . '" placeholder="' . esc_attr__( 'Search for post ...', 'posts-list' ) . '" autocomplete="OFF" />
        <a class="dashicons dashicons-edit" href="' . esc_url( get_edit_post_link( $post ) ) . '" target="_blank"></a>
        <i class="dashicons dashicons-no-alt"></i>
    </div>';
}

// save list events order)
add_action( 'admin_post_list-posts-order', function() {
	// check if our nonce is set and verify that the nonce is valid
	if ( wp_verify_nonce( $_POST['list-posts-order_nonce'] ?? '', 'list-posts-order_save' ) ) {
		// allowed taxonomies
		$list_taxonomies = array_map( fn( $post_type ) => "{$post_type}_list", apply_filters( 'register_list_taxonomy', [] ) );

		// sanitize: [TAXONOMY => [TERM_ID => [POST_ID, ...]]]
		$input = wp_unslash( $_POST['list-posts-order'] ?? [] );
		$data = [];
		foreach ( is_array( $input ) ? $input : [] as $taxonomy => $terms ) {
			$taxonomy = sanitize_key( $taxonomy );
			if ( !is_array( $terms ) || !in_array( $taxonomy, $list_taxonomies, true ) || !taxonomy_exists( $taxonomy ) ) continue;

			if ( !current_user_can( get_taxonomy( $taxonomy )->cap->assign_terms ) ) {
				wp_die( esc_html__( 'Sorry, you are not allowed to do this.' ), 403 );
			}

			foreach ( $terms as $term_id => $post_ids ) {
				$term_id = absint( $term_id );
				if ( !$term_id || !is_array( $post_ids ) || !term_exists( $term_id, $taxonomy ) ) continue;

				// remove empty entries
				$post_ids = array_values( array_filter( array_map( 'absint', $post_ids ) ) );

				foreach ( $post_ids as $post_id ) {
					if ( !current_user_can( 'edit_post', $post_id ) ) {
						wp_die( esc_html__( 'Sorry, you are not allowed to do this.' ), 403 );
					}
				}

				if ( $post_ids ) $data[$taxonomy][$term_id] = $post_ids;
			}
		}

        // save order
		if ( !$data = array_filter( $data ) ) delete_option( 'list-posts-order' );
		else update_option( 'list-posts-order', $data );

        // check if event is removed from list
        // -> remove event from taxonomy term (list)
        foreach ( apply_filters( 'register_list_taxonomy', [] ) as $post_type ) {
	        $list_taxonomy = get_taxonomy( "{$post_type}_list" );
	        if ( !$list_taxonomy || !current_user_can( $list_taxonomy->cap->assign_terms ) ) continue;
	        foreach ( get_terms( [
	            'taxonomy' => "{$post_type}_list",
	            'hide_empty' => false
            ] ) as $term ) {
		        foreach ( get_list_posts( $term ) as $post ) {
			        if ( in_array( $post->ID, $data["{$post_type}_list"][$term->term_id] ?? [] ) ) continue;
			        if ( !current_user_can( 'edit_post', $post->ID ) ) continue;
			        wp_remove_object_terms( $post->ID, $term->term_id, "{$post_type}_list" );
		        }
            }
        }

		// in case event was added in list
		// -> set event's taxonomy terms (must set all terms!)
		$object_terms = []; // [EVENT_ID => [TERM, TERM, ...]]
		foreach ( $data as $taxonomy => $terms ) {
            foreach ( $terms as $term_id => $post_ids ) {
	            foreach ( $post_ids as $post_id ) {
		            $object_terms[ $post_id ][ $taxonomy ][] = $term_id;
	            }
            }
		}

        // eventually set event list terms
        foreach ( $object_terms as $post_id => $taxonomy ) {
            foreach ( $taxonomy as $taxonomy_name => $terms ) {
	            wp_set_object_terms( $post_id, $terms, $taxonomy_name );
            }
        }
    }

    // redirect to dashboard
	wp_redirect( add_query_arg( ['message' => 'list-posts-order'], admin_url() ) );
	exit;
} );
