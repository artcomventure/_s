<?php

/**
 * Redirect author pages to 404 error page.
 */
add_action( 'template_redirect', function() {
	global $wp_query;

	if ( is_author() ) {
		$wp_query->set_404();
		status_header(404 );
	}
}, 1 );
