<?php

// more indistinct way to hide the honeypots
add_filter( 'wpcf7_honeypot_container_css', function( $css ) {
	return 'text-indent: 100%; white-space: nowrap; overflow: hidden; position: absolute; clip: rect(1px, 1px, 1px, 1px); clip-path: inset(50%); z-index: -1;';
} );

add_filter( 'wpcf7_autop_or_not', '__return_false' );
add_filter( 'wpcf7_load_css', '__return_false' );
