<?php

define( 'REST_API_DIRECTORY', dirname( __FILE__ ) );
define( 'REST_API_DIRECTORY_URI', UTILITIES_DIRECTORY_URI . '/inc/rest-api' );

// auto include /inc files
auto_include_files( dirname( __FILE__ ) . '/inc' );
