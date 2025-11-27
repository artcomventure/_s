/**
 * Let `[data-href]` behave like `<a>`.
 */
Behaviours.add( 'links:data-href', $context => {

    let $links = [].slice.call( $context.querySelectorAll( '[data-href]' ) );
    if ( !!$context.hasAttribute && $context.hasAttribute( 'data-href' ) ) $links.unshift( $context );

    $links.forEach( $link => {
        let dataHref = $link.getAttribute( 'data-href' );

        // links in data-href
        let $a = $link.querySelectorAll( 'a' );

        // replace placeholder with href of `<a>` inside `[data-href]`s element
        // format: `a:first`, `a:NUMBER` or `a:last`
        const match = dataHref.match( /a:(first|\d+|last)/ );
        if ( match ) {
            // FYI: no need to get link's `href` attributes right away.
            // `toString()` function will handle that.
            if ( match[1] === 'first' ) dataHref = $a[0];
            else if ( match[1] === 'last' ) dataHref = $a[a.length - 1];
            else if ( !isNaN( match[1] ) ) dataHref = $a[match[1] * 1 - 1];
            // no found
            else dataHref = '';

            // in this case `dataHref` is still the found `<a>` in `[data-href]`
            if ( dataHref && dataHref.hasAttribute( 'target' ) ) {
                $link.setAttribute( 'target', dataHref.getAttribute( 'target' ) );
            }
        }

        // no fake link ... remove all attributes
        if ( !dataHref ) return ['tabindex', 'role', 'data-href', 'target']
            .forEach( attribute => $link.removeAttribute( attribute ) );

        $link.setAttribute( 'data-Href', dataHref );

        $a.forEach( $a => {
            // don't propagate click event up to `$link`
            $a.addEventListener( 'click', function ( e ) {
                e.stopPropagation();
            }, false );

            // don't access same link inside
            if ( $a.href !== dataHref.toString() ) return;
            $a.setAttribute( 'tabindex', -1 );
        } )

        // make sure _link_ is accessible
        if ( !$link.hasAttribute( 'tabindex' ) )
            $link.setAttribute( 'tabindex', 0 );

        // ... and correctly _interpreted_
        $link.setAttribute( 'role', 'link' );

        // ---
        // redirect

        $link.addEventListener( 'click', e => {
            // is anchor
            if ( dataHref[0] === '#' ) {
                const $element = document.getElementById( dataHref );
                if ( $element ) {
                    $element.scrollIntoView();
                    // re-do scroll in case something changes on scroll action
                    setTimeout( () => $element.scrollIntoView(), 100 )
                }

                return;
            }

            if ( typeof pjax !== 'undefined' && isInternalUrl( dataHref ) ) {
                const $tmp = document.createElement( 'div' );
                $tmp.innerHTML = `<a href="${dataHref}"></a>`;
                // _pjax-valid_ URL!?
                if ( $tmp.querySelector( pjax.options.elements ) )
                    return pjax.loadUrl( dataHref );
            }

            window.open( dataHref, $link.getAttribute( 'target') || '_self' );
        }, false );

        $link.addEventListener( 'keydown', e => {
            if ( e.key !== 'Enter' ) return;
            $link.dispatchEvent( new Event( 'click' ) );
        }, false );
    } )

} );