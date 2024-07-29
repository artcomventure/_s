<?php

define( 'BODYCLASS_DIRECTORY', dirname( __FILE__ ) );
define( 'BODYCLASS_DIRECTORY_URI', GUTENBERG_DIRECTORY_URI . '/inc/bodyclass' );

// auto include /inc files
auto_include_files( BODYCLASS_DIRECTORY . '/inc' );
