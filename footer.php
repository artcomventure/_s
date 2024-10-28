<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package _s
 */

?>

	<footer id="colophon" class="site-footer">
        <div class="site-footer--content">
            <?php wp_nav_menu( [
                'theme_location' => 'footer',
                'container'      => false,
                'menu_id'        => 'footer-menu',
            ] ); ?>
            <?php printf( __( '&copy; %d %s', '_s' ), date( 'Y' ), get_bloginfo( 'name' ) ) ?>
        </div><!-- .footer-content -->
    </footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
