/**
 * Replace SVG image with actual SVG code.
 *
 * ```
 * <img src="path/to/image.svg" />
 * // output: <svg>...</svg>
 * ```
 */

Behaviours.add( 'media:inline-svg', function( $context ) {

    const replaceImage = function( $image ) {
        if ( !$image || /^data:/.test( $image.src ) ) return;

        const xhr = new XMLHttpRequest();
        xhr.open("GET", $image.src );
        xhr.onload = function (e) {
            if ( xhr.status === 200 && !!(xhr.responseXML||{}).documentElement ) {
                const $svg = xhr.responseXML.documentElement;

                // take over _all_ attributes
                [].forEach.call( $image.attributes, function( attribute ) {
                    // igmore _these_ attributes
                    if ( ['src'].indexOf( attribute.name ) >= 0 ) return;
                    // set classNames
                    else if ( attribute.name === 'class' )
                        $image.classList.forEach( ( className ) => $svg.classList.add( className ) );
                    // set all other attributes
                    else $svg.setAttribute( attribute.name, attribute.value );
                } );

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

        replaceImage( $image );
        $image.addEventListener( 'load', function( e ) {
            replaceImage( e.target );
        }, { passive: true } );
    } );

} );