<?php

/**
 * Add _above the fold_ CSS inline to head.
 */
add_action( 'wp_head', function() {

	if ( !file_exists( $file = get_template_directory() . '/css/above-the-fold.css' ) )
		return;

	if ( !$CSS = @file_get_contents( $file ) ) return;  ?>

    <style><?php echo $CSS ?></style>

<?php }, 0 );
