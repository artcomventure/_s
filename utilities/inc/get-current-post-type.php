<?php

// get current post type (front- and backend)
function get_current_post_type( $post = 0 ) {
    if ( !$post_type = get_post_type( $post ) ) {
        global $pagenow;

        if ( 'post-new.php' === $pagenow ) {
            if ( isset( $_REQUEST['post_type'] ) && post_type_exists( $_REQUEST['post_type'] ) ) {
                $post_type = $_REQUEST['post_type'];
            }
        } elseif ( 'post.php' === $pagenow ) {
            if ( isset( $_GET['post'] ) && isset( $_POST['post_ID'] ) && (int) $_GET['post'] !== (int) $_POST['post_ID'] ) {
                // Do nothing
            } elseif ( isset( $_GET['post'] ) ) {
                $post_id = (int) $_GET['post'];
            } elseif ( isset( $_POST['post_ID'] ) ) {
                $post_id = (int) $_POST['post_ID'];
            }

            if ( isset($post_id) ) {
                $post_type = get_post_type( $post_id );
            }
        }
    }

    return $post_type;
}