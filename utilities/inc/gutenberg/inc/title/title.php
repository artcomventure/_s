<?php

define( 'UTLTS_GUTENBERG_TITLE_DIRECTORY', dirname( __FILE__ ) );
define( 'UTLTS_GUTENBERG_TITLE_DIRECTORY_URI', UTLTS_GUTENBERG_DIRECTORY_URI . '/inc/title' );

// auto include /inc files
auto_include_files( UTLTS_GUTENBERG_TITLE_DIRECTORY . '/inc' );
