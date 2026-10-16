/**
 * Set `aria-label` with cleaned up text content (screenreader).
 * Only on elements named by their content: `aria-label` is ignored on generic elements like `p`, `div`, `span`.
 */
Behaviours.add( 'accessibility:shy', $context => {
    const selector = 'h1, h2, h3, h4, h5, h6, a, button, summary';

    const $elements = [...$context.querySelectorAll( selector )];
    if ( $context.matches?.( selector ) ) $elements.push( $context );

    $elements.forEach( $element => {
        // don't override existing `aria-label`
        if ( $element.hasAttribute( 'aria-label' ) ) return;
        // "&shy;" in any text node
        if ( !$element.textContent.includes( '­' ) ) return;

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