<?php

add_action( 'dev-info-panel', function() {
	$gitBasePath = '.git';
	$git = @file_get_contents( "{$gitBasePath}/HEAD" );
    if ( !$git ) return;

	$branch = rtrim( preg_replace("/(.*?\/){2}/", '', $git ) );
	$head = "{$gitBasePath}/refs/heads/{$branch}";

	$hash = file_get_contents( $head );
	$time = filemtime( $head ); ?>

	<div id="git-info" title="<?php printf( __( "Commit from %s o'clock", 'dev' ), wp_date( 'd.m.Y H:i', $time ) ) ?>">
		<span data-copy="Branch" title="<?php _e( 'Branch', 'dev' ) ?>"><?php echo $branch ?></span>
        <span data-copy="Commit hash" title="<?php printf( __( 'Hash: %s', 'dev' ), $hash ) ?>"><?php echo substr( $hash, 0, 8 ) ?></span>
	</div>

	<?php
}, 11 );

