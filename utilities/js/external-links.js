/**
 * Open external links in new window.
 *
 * Also add link UX to none `<a>` elements by setting `data-href` attribute.
 */

Behaviours.add( 'links:external', $context => {
    let $links = [].slice.call( $context.querySelectorAll( 'a, [data-href]' ) );
    if ( !!$context.hasAttribute && $context.hasAttribute( 'data-href' ) || $context.tagName === 'A' ) $links.unshift( $context );

    $links.forEach( $link => {
        const isChildOfDataHref = $link.parentElement.closest( '[data-href]' );
        const isChildOfA = $link.parentElement.closest( 'a' );
        if ( isChildOfDataHref || isChildOfA )
            $link.addEventListener( 'click', e => {
                e.stopPropagation();
                if ( isChildOfA ) e.preventDefault();
            }, false );

        // internal!? ... nothing to do
        if ( isInternalUrl( $link.href || $link.getAttribute( 'data-href' ) ) ) return;

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