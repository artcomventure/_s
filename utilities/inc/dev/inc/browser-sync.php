<?php

/**
 * Enables npm's browser sync.
 * @see neukoellneroper/package.json
 */

if ( !isLocal() ) return;

add_action( 'wp_footer', function() {
    if ( is_admin() ) return; ?>

	<script id="__bs_script__">//<![CDATA[
        document.write( '<script async src="http://' + location.hostname + ':3000/browser-sync/browser-sync-client.js?v=2.27.10"><\/script>' );
    //]]></script>
<?php }, 11 );