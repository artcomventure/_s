<?php

/**
 * Enables npm's browser sync.
 * @see THEME/package.json
 */

if ( !isLocal() ) return;

add_action( 'wp_footer', function() {
    if ( is_admin() ) return;

    // check if BrowserSync is actually running to avoid browser timeout
    // @since 1.20.3
    foreach ( [
        explode( ':', $_SERVER['HTTP_HOST'] )[0],
        'host.docker.internal',
        '172.17.0.1',
        'localhost'
    ] as $host ) {
        if ( $socket = @fsockopen( $host, 3000, $errno, $errstr, .1 ) ) { ?>
            <script id="__bs_script__">//<![CDATA[
                document.write( '<script async src="http://' + location.hostname + ':3000/browser-sync/browser-sync-client.js?v=2.27.10"><\/script>' );
            //]]></script>

            <?php break;
        }
    }

    if ( $socket ?? false ) fclose( $socket );
}, 11 );