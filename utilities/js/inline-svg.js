/**
 * Replace SVG image with actual SVG code.
 *
 * ```
 * <img src="path/to/image.svg" />
 * // output: <svg>...</svg>
 * ```
 */

Behaviours.add( 'media:inline-svg', function( $context ) {

    // remove scripts and event handlers: SVG is inserted into the live document
    const sanitizeSvg = function( $svg ) {
        $svg.querySelectorAll( 'script, foreignObject' ).forEach( $element => $element.remove() );

        [$svg].concat( Array.from( $svg.querySelectorAll( '*' ) ) ).forEach( $element => {
            // e.g. <set attributeName="href" to="javascript:…">
            if ( ['set', 'animate'].includes( $element.localName ) && /href/i.test( $element.getAttribute( 'attributeName' ) || '' ) )
                return $element.remove();

            Array.from( $element.attributes ).forEach( attribute => {
                if ( /^on/i.test( attribute.name ) || ( /href$/i.test( attribute.name ) && /^\s*javascript:/i.test( attribute.value ) ) )
                    $element.removeAttribute( attribute.name );
            } );
        } );
    };

    const replaceImage = function( $image ) {
        if ( !$image || /^data:/.test( $image.src ) ) return;

        const xhr = new XMLHttpRequest();
        xhr.open("GET", $image.src );
        xhr.onload = function (e) {
            if ( xhr.status === 200 && !!(xhr.responseXML||{}).documentElement ) {
                const $svg = xhr.responseXML.documentElement;
                sanitizeSvg( $svg );

                // take over _all_ attributes
                [].forEach.call( $image.attributes, function( attribute ) {
                    // ignore _these_ attributes
                    if ( ['src'].indexOf( attribute.name ) >= 0 ) return;
                    // set classNames
                    else if ( attribute.name === 'class' )
                        $image.classList.forEach( ( className ) => $svg.classList.add( className ) );
                    else if ( attribute.name === 'alt' && attribute.value ) {
                        $svg.setAttribute( 'aria-label', attribute.value );
                    }
                    // set all other attributes
                    else $svg.setAttribute( attribute.name, attribute.value );
                } );

                // no alt → no aria-label → decorative
                if ( !$svg.hasAttribute( 'aria-label' ) )
                    $svg.setAttribute( 'aria-hidden', 'true' );

                $svg.setAttribute( 'role', 'img' );
                $svg.setAttribute( 'preserveAspectRatio', 'xMidYMid' );

                $svg.classList.remove( 'inline-svg', 'lazyload', 'lazyloading' );

                // replace image with svg
                if ( $image && $image.parentElement ) {
                    $image.parentElement.replaceChild( $svg, $image );

                    const $figure = $svg.closest( '.inline-svg' );
                    if ( $figure ) $figure.classList.remove( 'inline-svg' );

                    window.requestAnimationFrame( () => $svg.dispatchEvent( new CustomEvent( 'inline-svg', { detail: $svg, bubbles: true } ) ) );
                }
            }
        }
        xhr.send("");
    };

    let $images; // get images
    if ( ['HTMLCollection', 'NodeList'].indexOf( $context.constructor.name ) >= 0 ) $images = $context;
    else $images = $context.getElementsByTagName( 'img' );

    [].forEach.call( $images, function( $image ) {
        if ( !($image.classList.contains( 'inline-svg' ) || $image.closest( '.inline-svg' )) ) return;

        if ( $image.complete ) replaceImage( $image );
        else $image.addEventListener( 'load', function( e ) {
            replaceImage( e.target );
        }, { passive: true } );
    } );

} );