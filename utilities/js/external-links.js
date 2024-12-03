/**
 * Open external links in new window.
 *
 * Also add link UX to none `<a>` elements by setting `data-href` attribute.
 */

Behaviours.add( 'link:external', $context => {
    // regexp for internal link
    const regexp = new RegExp( '^(\/$|\/[^\/]|#|((ht|f)tps?:)?\/\/' + location.host + '|javascript:)' );

    const $links = $context.querySelectorAll( 'a, [data-href]' );
    if ( ($context.tagName||'') === 'A' || (!!$context.hasAttribute && $context.hasAttribute( 'data-href' )) )
        $links.push( $context );

    $links.forEach(function ( $link ) {
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

            if ( dataHref ) {
                if ( !$link.hasAttribute( 'role' ) ) $link.setAttribute( 'role', 'link' );
                if ( !$link.hasAttribute( 'tabindex' ) ) $link.setAttribute( 'tabindex', 0 );

                $link.addEventListener( 'click', function () {
                    window.open( dataHref, $link.getAttribute( 'target') || '_self' );
                }, false );

                // redirect on enter
                $link.addEventListener( 'keydown', e => {
                    if ( e.key !== 'Enter' ) return;
                    $link.dispatchEvent( new Event( 'click' ) );
                }, false );

                // remove same inner links from tab navigations
                [].forEach.call( $link.querySelectorAll( 'a, [data-href]' ), $a => {
                    if ( dataHref !== $a.href ) return;
                    if ( !$a.hasAttribute( 'tabindex' ) ) $a.setAttribute( 'tabindex', -1 );
                } )
            }

            if ( regexp.test( $link.getAttribute( 'data-href' ) ) ) return;
        }

        // open external links in new window
        if ( !$link.getAttribute( 'target' ) )
            $link.setAttribute( 'target', '_blank' );
    } );
} );