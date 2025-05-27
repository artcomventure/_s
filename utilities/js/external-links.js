/**
 * Open external links in new window.
 */

Behaviours.add( 'link:external', $context => {
    const $links = [].slice.call( $context.querySelectorAll( 'a, [data-href]' ) );
    if ( ($context.tagName||'') === 'A' || (!!$context.hasAttribute && $context.hasAttribute( 'data-href' )) )
        $links.push( $context );

    $links.forEach( $link => {
        if ( isInternalUrl( $link.href || $link.getAttribute( 'data-href' ) ) ) return;

        if ( !$link.hasAttribute( 'rel' ) )
            $link.setAttribute( 'rel', 'noopener noreferrer' );

        // open external links in new window
        if ( !$link.getAttribute( 'target' ) )
            $link.setAttribute( 'target', '_blank' );
    } );
} );