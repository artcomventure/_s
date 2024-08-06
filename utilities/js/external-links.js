/**
 * Open external links in new window.
 *
 * Also add link UX to none `<a>` elements by setting `data-href` attribute.
 */

Behaviours.add( 'links:external', $context => {
    // regexp for internal link
    const regexp = new RegExp( '^(\/$|\/[^\/]|#|((ht|f)tps?:)?\/\/' + location.host + '|javascript:)' );

    let $links = [].slice.call( $context.querySelectorAll( 'a, [data-href]' ) );
    if ( !!$context.hasAttribute && $context.hasAttribute( 'data-href' ) || $context.tagName === 'A' ) $links.unshift( $context );

    $links.forEach( $link => {
        if ( $link.closest( '[data-href]' ) || $link.closest( 'a' ) )
            $link.addEventListener( 'click', e => {
                e.stopPropagation();
            }, false );

        // internal!? ... nothing to do
        if ( regexp.test( $link.href || $link.getAttribute( 'data-href' ) ) ) return;

        // ---
        // external

        if ( $link.tagName === 'A' && !$link.hasAttribute( 'rel' ) ) {
            $link.setAttribute( 'rel', 'noopener noreferrer' );
        }

        // open in new window
        if ( !$link.getAttribute( 'target' ) )
            $link.setAttribute( 'target', '_blank' );
    } );
} );