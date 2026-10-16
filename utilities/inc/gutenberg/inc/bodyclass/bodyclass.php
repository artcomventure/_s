<?php

define( 'UTLTS_GUTENBERG_BODYCLASS_DIRECTORY', dirname( __FILE__ ) );
define( 'UTLTS_GUTENBERG_BODYCLASS_DIRECTORY_URI', UTLTS_GUTENBERG_DIRECTORY_URI . '/inc/bodyclass' );

// auto include /inc files
auto_include_files( UTLTS_GUTENBERG_BODYCLASS_DIRECTORY . '/inc' );
