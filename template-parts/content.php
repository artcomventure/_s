<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package _s
 */

global $more;
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php // don't include header if `<h1>` title block is already added to content
	if ( !hasH1( parse_blocks( $post->post_content ) ) ) : ?>
        <header class="entry-header">
			<?php $titletag = is_singular( get_post_type() ) && $more ? 'h1' : 'h2';
			the_post_title( '<' . $titletag . ' class="entry-title">', '</' . $titletag . '>' ); ?>

            <?php if ( 'post' === get_post_type() ) : ?>
                <div class="entry-meta">
                    <?php _s_posted_on();
                    _s_posted_by(); ?>
                </div><!-- .entry-meta -->
	        <?php endif; ?>
        </header><!-- .entry-header -->
	<?php endif; ?>

	<?php _s_post_thumbnail(); ?>

	<div class="entry-content">
		<?php
		the_content(
			sprintf(
				wp_kses(
					/* translators: %s: Name of current post. Only visible to screen readers */
					__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', '_s' ),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				wp_kses_post( get_the_title() )
			)
		);

		wp_link_pages(
			array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', '_s' ),
				'after'  => '</div>',
			)
		);
		?>
	</div><!-- .entry-content -->

	<footer class="entry-footer">
		<?php _s_entry_footer(); ?>
	</footer><!-- .entry-footer -->
</article><!-- #post-<?php the_ID(); ?> -->
