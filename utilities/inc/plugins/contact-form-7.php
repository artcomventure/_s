<?php

// more indistinct way to hide the honeypots
add_filter( 'wpcf7_honeypot_container_css', function( $css ) {
	return 'text-indent: 100%; white-space: nowrap; overflow: hidden; position: absolute; z-index: -1;';
} );

// no wpautop on forms
add_filter('wpcf7_autop_or_not', '__return_false');
