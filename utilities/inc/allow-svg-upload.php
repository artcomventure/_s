<?php // https://blog.kulturbanause.de/2013/05/svg-dateien-in-die-wordpress-mediathek-hochladen/

add_filter( 'upload_mimes', function( $mimes ){
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
} );

add_filter( 'wp_check_filetype_and_ext', function( $checked, $file, $filename, $mimes ) {
	if( !$checked['type'] ) {
		$wp_filetype = wp_check_filetype( $filename, $mimes );
		$ext = $wp_filetype['ext'];
		$type = $wp_filetype['type'];
		$proper_filename = $filename;

		if($type && 0 === strpos( $type, 'image/' ) && $ext !== 'svg' ) {
			$ext = $type = false;
		}

		$checked = compact('ext','type','proper_filename' );
	}

	return $checked;
}, 10, 4 );
