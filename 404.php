<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package _s
 */

get_header();
?>

    <main id="primary" class="site-main">
	    <?php if ( $not_found = get_404_page() ) :

		    $not_found = new WP_Query( 'page_id=' . $not_found );

		    while ( $not_found->have_posts() ) : $not_found->the_post();
			    get_template_part( 'template-parts/content', '404' );
		    endwhile;

		    // restore original post data
		    wp_reset_postdata();
	    else: ?>
            <section class="error-404 not-found">
                <header class="page-header">
                    <h1 class="page-title"><?php esc_html_e( 'Oops! That page can&rsquo;t be found.', '_s' ); ?></h1>
                </header><!-- .page-header -->

                <div class="page-content">
                    <p><?php esc_html_e( 'It looks like nothing was found at this location.', '_s' );
                        if ( !get_option( 'disable_posts' ) ) printf(
                            esc_html( __( 'Maybe try one of the links below%s?', '_s' ) ),
                            !get_option('disable_search' ) ? ' ' . __( 'or a search', '_s' ) : ''
                        )?></p>

                    <?php if ( !get_option('disable_search' ) ) get_search_form();

                    if ( !get_option( 'disable_posts' ) ) :
                        the_widget( 'WP_Widget_Recent_Posts' ); ?>

                        <div class="widget widget_categories">
                            <h2 class="widget-title"><?php esc_html_e( 'Most Used Categories', '_s' ); ?></h2>
                            <ul>
                                <?php wp_list_categories( [
                                    'orderby'    => 'count',
                                    'order'      => 'DESC',
                                    'show_count' => 1,
                                    'title_li'   => '',
                                    'number'     => 10,
                                ] ); ?>
                            </ul>
                        </div><!-- .widget -->

                        <?php /* translators: %1$s: smiley */
                        $_s_archive_content = '<p>' . sprintf( esc_html__( 'Try looking in the monthly archives. %1$s', '_s' ), convert_smilies( ':)' ) ) . '</p>';
                        the_widget( 'WP_Widget_Archives', 'dropdown=1', "after_title=</h2>$_s_archive_content" );

                        the_widget( 'WP_Widget_Tag_Cloud' );
                    endif; ?>

                </div><!-- .page-content -->
            <?php endif; ?>
        </section>
    </main><!-- #main -->

<?php
get_footer();
