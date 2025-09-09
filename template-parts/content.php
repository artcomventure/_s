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

<article id="post-<?php the_ID(); ?>" <?php post_class( $args['post_class'] ?? '' ); ?>
	<?php echo (!$more && is_post_publicly_viewable()) ? 'data-href="' . get_permalink() . '"' : '' ?>>
	<?php // don't include header if `<h1>` title block is already added to content
	if ( !$more || !hasH1( parse_blocks( $post->post_content ) ) ) : ?>
        <header <?php header_class( 'entry-header' ) ?>>
			<?php $titletag = $args['title_tag'] ?? ($more ? 'h1' : 'h2');

			$before = "<$titletag class=\"entry-title\">";
			$after = "</$titletag>";
			if ( !$more && is_post_publicly_viewable() ) {
				$before .= '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">';
				$after = "</a>$after";
			}

			the_post_title( $before, $after );

			$titletag = $titletag === 'h1' ? 'h2' : 'h3';
			the_subtitle( "<$titletag class=\"entry-subtitle\">", "</$titletag>" ); ?>

            <?php if ( 'post' === get_post_type() ) : ?>
                <div class="entry-meta">
                    <?php _s_posted_on();
                    _s_posted_by(); ?>
                </div><!-- .entry-meta -->
	        <?php endif; ?>
        </header><!-- .entry-header -->
	<?php endif; ?>

	<?php if ( !$more || !get_post_meta( get_the_ID(), '_hide_thumbnail', true ) )
        _s_post_thumbnail(); ?>

	<div class="entry-content">
		<?php the_content( sprintf(
			wp_kses(
				/* translators: %s: Name of current post. Only visible to screen readers */
				__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', '_s' ),
                ['span' => ['class' => []]]
			),
			wp_kses_post( get_the_title() )
		) );

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
