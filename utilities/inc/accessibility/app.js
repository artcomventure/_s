/**
 * Set `aria-label` with cleaned up text content.
 */
Behaviours.add( 'accessibility:shy', $context => {

    const shy = '­'; // &shy;

    // get all elements with "&shy;" in text
    const elements = document.evaluate(
        `/html/body//*[contains(text(), '${shy}')]`,
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
        $element.setAttribute(
            'aria-label', Alter.do( 'accessibility:shy', $element.cloneNode( true ) ).textContent.replace( shy, '' ).trim()
        )
    } );

} )