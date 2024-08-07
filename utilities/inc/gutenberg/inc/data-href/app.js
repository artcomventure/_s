/**
 * Let `[data-href]` behave like `<a>`.
 */
Behaviours.add( 'links:data-href', $context => {

    let $links = [].slice.call( $context.querySelectorAll( '[data-href]' ) );
    if ( !!$context.hasAttribute && $context.hasAttribute( 'data-href' ) ) $links.unshift( $context );

    $links.forEach( $link => {
        let dataHref = $link.getAttribute( 'data-href' );

        // replace placeholder with href of `<a>` inside `[data-href]`s element
        // format: `a:first`, `a:NUMBER` or `a:last`
        const match = dataHref.match( /a:(first|\d+|last)/ );
        if ( match ) {
            let $a = [].slice.call( $link.querySelectorAll( 'a' ) );
            // FYI: no need to get link's `href` attributes right away.
            // `toString()` function will handle that.
            if ( match[1] === 'first' ) dataHref = $a.shift();
            else if ( match[1] === 'last' ) dataHref = $a.pop();
            else if ( !isNaN( match[1] ) ) dataHref = $a[match[1] * 1 - 1];
            // no found
            else dataHref = '';
        }

        // no fake link ... remove all attributes
        if ( !dataHref ) return ['tabindex', 'role', 'data-href', 'target']
            .forEach( attribute => $link.removeAttribute( attribute ) );

        $link.setAttribute( 'data-Href', dataHref );

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