<?php

/**
 * Allow zip files to send as safe (no `_.txt` ending).
 * @see `ROOT/wp-content/plugins/contact-form-7/includes/formatting.php:wpcf7_antiscript_file_name()`
 *
 * @since 2.2.0
 */
add_filter( 'wpcf7_file_extension_safelist', function( $safelist ) {
	return array_merge( $safelist, ['zip'] );
} );
