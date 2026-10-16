<?php

add_action( 'dev-info-panel', function() {
    $gitBasePath = '.git';

    if ( ! file_exists( $gitBasePath ) ) {
        // @since 1.20.3
        if ( defined( 'ABSPATH' ) && file_exists( dirname( ABSPATH ) . "/$gitBasePath" ) )
            $gitBasePath = dirname( ABSPATH ) . "/$gitBasePath";
        // fallback: theme root @since 1.21
        elseif ( file_exists( get_stylesheet_directory() . "/$gitBasePath" ) )
            $gitBasePath = get_stylesheet_directory() . "/$gitBasePath";
    }

	if ( !file_exists( "{$gitBasePath}/HEAD" ) ) return;
	if ( !$git = @file_get_contents( "{$gitBasePath}/HEAD" ) ) return;

	$branch = rtrim( preg_replace("/(.*?\/){2}/", '', $git ) );
	$head = "{$gitBasePath}/refs/heads/{$branch}";

	if ( $hash = @file_get_contents( $head ) )
		$time = filemtime( $head );

	// repository of remote `origin` @since 1.21
	$config = @file_get_contents( "{$gitBasePath}/config" );
	if ( $config && preg_match( '/\[remote "origin"\][^\[]*?\burl\s*=\s*(\S+)/', $config, $match ) ) {
		// strip credentials (`https://user:token@host/…`)
		$repoUrl  = preg_replace( '#^(\w+://)[^/@]+@#', '$1', $match[1] );
		$repoName = basename( $repoUrl, '.git' );

		// web URL from `https://host/path`, `ssh://git@host[:port]/path` or `git@host:path`
		$repoLink = null;
		if ( preg_match( '#^(?:https?|ssh)://([^/:]+)(?::\d+)?/(.+?)(?:\.git)?/?$#', $repoUrl, $parts )
			|| preg_match( '#^[^@/]+@([^:/]+):(.+?)(?:\.git)?/?$#', $repoUrl, $parts ) )
			$repoLink = "https://{$parts[1]}/{$parts[2]}";
	}

    $gitInfoTitle = isset($time) ? ' title="' . esc_attr( sprintf( __( "Commit from %s o'clock", 'dev' ), wp_date( 'd.m.Y H:i', $time ) ) ) . '"' : ''?>

    <div id="git-info"<?php echo $gitInfoTitle ?>>
		<?php if ( isset( $repoName ) ) : ?>
            <span data-copy="Repository" title="<?php echo esc_attr( sprintf( __( 'Repository: %s', 'dev' ), $repoLink ?? $repoUrl ) ) ?>">
                <?php if ( $repoLink ) : ?><a href="<?php echo esc_url( $repoLink ) ?>" target="_blank" rel="noopener noreferrer"><?php endif; ?>
                    <?php echo esc_html( $repoName ) ?> <span class="sr-only">(<?php echo esc_html( $repoLink ?? $repoUrl ) ?>)</span>
                <?php if ( $repoLink ) : ?></a><?php endif; ?>
            </span>
		<?php endif; ?>
        <span data-copy="Branch" title="<?php esc_attr_e( 'Branch', 'dev' ) ?>"><?php echo esc_html( $branch ) ?></span>
		<?php if ( $time ?? false ) : ?>
            <span data-copy="Commit hash" title="<?php echo esc_attr( sprintf( __( 'Hash: %s', 'dev' ), trim( $hash ) ) ) ?>"><?php echo esc_html( substr( $hash, 0, 8 ) ) ?></span>
		<?php endif; ?>
    </div>

	<?php
}, 11 );

