<?php

add_action( 'dev-info-panel', function() {
	$gitBasePath = '.git';

    // @since 1.20.3
    if ( ! file_exists( $gitBasePath ) && defined( 'ABSPATH' ) && file_exists( dirname( ABSPATH ) . '/.git' ) ) {
        $gitBasePath = dirname( ABSPATH ) . '/.git';
    }

	if ( !file_exists( "{$gitBasePath}/HEAD" ) ) return;
	if ( !$git = @file_get_contents( "{$gitBasePath}/HEAD" ) ) return;

	$branch = rtrim( preg_replace("/(.*?\/){2}/", '', $git ) );
	$head = "{$gitBasePath}/refs/heads/{$branch}";

	if ( $hash = @file_get_contents( $head ) )
		$time = filemtime( $head );

    $gitInfoTitle = isset($time) ? ' title="' . sprintf( __( "Commit from %s o'clock", 'dev' ), wp_date( 'd.m.Y H:i', $time ) ) . '"' : ''?>

    <div id="git-info"<?php echo $gitInfoTitle ?>>
        <span data-copy="Branch" title="<?php _e( 'Branch', 'dev' ) ?>"><?php echo $branch ?></span>
		<?php if ( $time ?? false ) : ?>
            <span data-copy="Commit hash" title="<?php printf( __( 'Hash: %s', 'dev' ), $hash ) ?>"><?php echo substr( $hash, 0, 8 ) ?></span>
		<?php endif; ?>
    </div>

	<?php
}, 11 );

