<?php

/**
 * Enables npm's browser sync.
 * @see THEME/package.json
 */

if ( !isLocal() ) return;

add_action( 'wp_footer', function() {
    if ( is_admin() ) return;

    // same port as `--port` in `THEME/package.json` (not 3000: often used by other apps)
    $port = (int) apply_filters( 'browser_sync_port', 3456 );

    // check if BrowserSync is actually running to avoid browser timeout
    // @since 1.20.3
    foreach ( [
            explode( ':', $_SERVER['HTTP_HOST'] )[0],
            'host.docker.internal',
            '172.17.0.1',
            'localhost'
    ] as $host ) {
        if ( $socket = @fsockopen( $host, $port, $errno, $errstr, .1 ) ) { ?>
            <script id="__bs_script__">//<![CDATA[
                document.write( '<script async src="http://' + location.hostname + ':<?php echo $port ?>/browser-sync/browser-sync-client.js?v=2.27.10"><\/script>' );
                //]]></script>

            <style>#__bs_notify__ { font-size: 12px !important; padding: .5em 1em !important; opacity: .65; }</style>
            <?php

            break;
        }
    }

    if ( $socket ?? false ) fclose( $socket );
}, 11 );