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
                global $more;
                $_more = $more;
                $more = 1;
                locate_template( [
                    'template-parts/content-404.php',
                    'template-parts/content-page.php',
                    'template-parts/content.php',
                ], true, false );
                $more = $_more;
		    endwhile;

		    // restore original post data
		    wp_reset_postdata();
	    else: ?>
            <section class="error-404 not-found container-content">
                <header class="page-header">
                    <h1 class="page-title"><?php esc_html_e( '404 — The page was not found.', '_s' ); ?></h1>
                </header><!-- .page-header -->

                <div class="page-content">
                    <div class="wp-block-buttons">
                        <div class="wp-block-button">
                            <a href="<?php echo esc_url( home_url() ) ?>" class="wp-block-button__link wp-element-button">
                                <?php _e( 'Back to home', '_s' ) ?>
                            </a>
                        </div>
                    </div>
                </div><!-- .page-content -->
            </section><!-- .error-404 -->
        <?php endif; ?>
    </main><!-- #main -->

<?php
get_footer();
