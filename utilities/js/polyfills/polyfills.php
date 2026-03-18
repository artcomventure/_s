<?php

/**
 * !!! This file isn't imported !!! and therefor not used.
 *
 * Browser which have no `ResizeObserver` or `IntersectionObserver` support
 * have also a lack of WP support. So no need at all trying to fix it.
 *
 * @since 1.20.3
 */

define( 'UTILITIES_JS_POLYFILLS_DIRECTORY', dirname( __FILE__ ) );
const UTILITIES_JS_POLYFILLS_DIRECTORY_URI = UTILITIES_DIRECTORY_URI . '/js/polyfills';

/**
 * Conditionally add polyfills.
 */
add_action( 'wp_head', function() {
    ob_start(); ?>

    <script id="polyfill-checker-inline-js">
        (function() {
            var supportsModernSyntax = true;

            try {
                new Function( 'const x = {}; x?.y;' );
                supportsModernSyntax = true;
            } catch (e) {}

            if ( !supportsModernSyntax ) {
                var $plyfll = document.createElement( 'script' );
                $plyfll.src = "<?php echo includes_url( 'js/dist/vendor/wp-polyfill.min.js' ); ?>";
                $plyfll.async = false;
                document.head.appendChild( $plyfll );
            }
        })();

        if ( !( 'IntersectionObserver' in window ) ) {
            var $io = document.createElement( 'script' );
            $io.src = "<?php echo esc_url( UTILITIES_JS_POLYFILLS_DIRECTORY_URI . '/intersection-observer.js?ver=0.12.2' ) ?>";
            $io.async = false;
            document.head.appendChild( $io );
        }

        if ( !( 'ResizeObserver' in window ) ) {
            var $ro = document.createElement( 'script' );
            $ro.src = "<?php echo esc_url( UTILITIES_JS_POLYFILLS_DIRECTORY_URI . '/resize-observer.umd.js?ver=3.4.0' ) ?>";
            $ro.async = false;
            document.head.appendChild( $ro );
        }
    </script>

    <?php echo minify_js( ob_get_clean() );
}, -2 );