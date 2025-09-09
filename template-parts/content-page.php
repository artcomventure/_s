<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package _s
 */

global $more;
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>
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
        </header><!-- .entry-header -->
	<?php endif; ?>

	<?php if ( !$more || !get_post_meta( get_the_ID(), '_hide_thumbnail', true ) )
		_s_post_thumbnail(); ?>

	<div class="entry-content">
		<?php the_content(); ?>
	</div><!-- .entry-content -->
</article><!-- #post-<?php the_ID(); ?> -->
