/**
 * Set `aria-label` with cleaned up text content (screenreader).
 */
Behaviours.add( 'accessibility:shy', $context => {

    // get all elements with "&shy;" in text
    const elements = document.evaluate(
        `//*[contains(., '­')]`,
        document, null, XPathResult.ANY_TYPE, null
    );

    // XPathResult to array
    const $elements = []; let $element;
    while ( $element = elements.iterateNext() ) {
        // don't override existing `aria-label`
        if ( $element.hasAttribute( 'aria-label' ) ) return;
        // `setAttribute` can't be done here.
        // `$element` must not be changed while in loop.
        $elements.push( $element );
    }

    // clean up text content and set as `aria-label`
    $elements.forEach( $element => {
        // clone element for possible cleanup
        const $el = $element.cloneNode( true );

        // replace new lines with space
        $el.innerHTML = $el.innerHTML.replace( /(<br ?\/?>)+/gi, ' ' );

        $element.setAttribute( 'aria-label', Alter.do( 'accessibility:shy', $el )
            // eventually remove
            .textContent.replace( /(&shy;|­|&#173;)/gi, '' ).trim()
        )
    } );

} )