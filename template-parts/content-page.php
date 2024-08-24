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

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php // don't include header if `<h1>` title block is already added to content
	if ( !$more || !hasH1( parse_blocks( $post->post_content ) ) ) : ?>
        <header class="entry-header">
			<?php $titletag = is_singular( 'page' ) && $more ? 'h1' : 'h2';

			$before = "<$titletag class=\"entry-title\">";
			$after = "</$titletag>";
			if ( !$more ) {
				$before .= '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">';
				$after = "</a>$after";
			}

			the_post_title( $before, $after );?>
        </header><!-- .entry-header -->
	<?php endif; ?>

	<?php _s_post_thumbnail(); ?>

	<div class="entry-content">
		<?php the_content(); ?>
	</div><!-- .entry-content -->
</article><!-- #post-<?php the_ID(); ?> -->
