// namespace for utilities' helpers (load order of the files isn't guaranteed)
window.utlts = window.utlts || {};

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
        if ( !utlts.isExternalUrl( $link.href || $link.getAttribute( 'data-href' ) ) ) return;

        if ( !$link.hasAttribute( 'rel' ) )
            $link.setAttribute( 'rel', 'noopener noreferrer' );

        // open external links in new window
        if ( !$link.getAttribute( 'target' ) )
            $link.setAttribute( 'target', '_blank' );
    } );
} );

/**
 * `isInternalUrl( url )`: http(s) on the current host.
 * `isExternalUrl( url )`: http(s) or ftp on another host.
 * `mailto:`, `tel:`, `javascript:` etc. are neither internal nor external.
 */
Object.assign( utlts, (() => {
    // `null` for invalid URLs
    const parse = url => {
        try {
            return new URL( url, location.href );
        }
        catch {
            return null;
        }
    }

    return {
        isInternalUrl: url => {
            if ( !url ) return true;

            const Url = parse( url );
            return !!Url && /^https?:$/.test( Url.protocol ) && Url.host === location.host;
        },

        isExternalUrl: url => {
            const Url = url && parse( url );
            return !!Url && /^(https?|ftp):$/.test( Url.protocol ) && Url.host !== location.host;
        }
    };
})() );

// deprecated global names (as `window` properties, not `const`: no clash with other scripts) – use `utlts.*`
['isInternalUrl', 'isExternalUrl'].forEach( name => window[name] = utlts[name] );
