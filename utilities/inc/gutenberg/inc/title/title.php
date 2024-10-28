<?php

define( 'TITLE_DIRECTORY', dirname( __FILE__ ) );
define( 'TITLE_DIRECTORY_URI', GUTENBERG_DIRECTORY_URI . '/inc/title' );

// auto include /inc files
auto_include_files( TITLE_DIRECTORY . '/inc' );
