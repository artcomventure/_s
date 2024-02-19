// ---
// - pen external links in new window

Behaviours.add( 'links:external', $context => {
    // regexp for internal link
    const regexp = new RegExp( '^(\/$|\/[^\/]|#|((ht|f)tps?:)?\/\/' + location.host + '|javascript:)' );

    [].forEach.call( $context.querySelectorAll( 'a, [data-href]' ), function ( $link ) {
        if ( $link.closest( '[data-href]' ) )
            $link.addEventListener( 'click', function ( e ) {
                e.stopPropagation();
            }, false );

        if ( $link.tagName === 'A' ) {
            // internal
            if ( regexp.test( $link.href ) ) return;

            if ( !$link.hasAttribute( 'rel' ) )
                $link.setAttribute( 'rel', 'noopener noreferrer' );
        }
        else {
            // let fake links ([data-href]) behave like their `<a>`
            const dataHref = $link.getAttribute( 'data-href' );
            if ( dataHref ) $link.addEventListener( 'click', function () {
                window.open( dataHref, $link.getAttribute( 'target') || '_self' );
            }, false );

            if ( regexp.test( $link.getAttribute( 'data-href' ) ) ) return;
        }

        // open external links in new window
        if ( !$link.getAttribute( 'target' ) )
            $link.setAttribute( 'target', '_blank' );
    } );
} );