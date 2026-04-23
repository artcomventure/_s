/**
 * Autogenerate a Google Maps link on `<address>` elements.
 *
 * @since 1.20.5
 */
Behaviours.add( 'link:address', $context => {
    $context.querySelectorAll( 'address:not([data-href])' ).forEach( $address => {
        let address = $address.innerText.replace( /(<br ?\/?>|\n)/g, ' ' ).trim();
        if ( !address ) return;

        $address.setAttribute( 'data-href', Alter.do( 'link:address',
            `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent( address )}`,
            encodeURIComponent( address ), address, $address
        ) )
    } )
} )

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

/**
 * Check if given `url` is internal.
 *
 * @param url
 * @returns {boolean}
 */
const isInternalUrl = url => {
    const regexp = new RegExp( '^(\/$|\/[^\/]|#|((ht|f)tps?:)?\/\/' + location.host + '|javascript:)' );
    return !url || regexp.test( url )
}

/**
 * Check if given `url` is external.
 *
 * @param url
 * @returns {boolean}
 */
const isExternalUrl = url => {
    return !isInternalUrl( url );
}