/**
 * Let `[data-href]` behave like `<a>`.
 */
Behaviours.add( 'links:data-href', $context => {

    let $links = [].slice.call( $context.querySelectorAll( '[data-href]' ) );
    if ( !!$context.hasAttribute && $context.hasAttribute( 'data-href' ) ) $links.unshift( $context );

    $links.forEach( $link => {
        const dataHref = $link.getAttribute( 'data-href' );
        if ( !dataHref ) return $link.setAttribute( 'tabindex', -1 );

        // make sure _link_ is accessible
        if ( !$link.hasAttribute( 'tabindex' ) )
            $link.setAttribute( 'tabindex', 0 );

        // ... and correctly _interpreted_
        $link.setAttribute( 'role', 'link' );

        // ---
        // redirect

        $link.addEventListener( 'click', e => {
            window.open( dataHref, $link.getAttribute( 'target') || '_self' );
        }, false );

        $link.addEventListener( 'keydown', e => {
            if ( e.key !== 'Enter' ) return;
            $link.dispatchEvent( new Event( 'click' ) );
        }, false );
    } )

} );