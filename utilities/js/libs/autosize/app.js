// ---
// Attach textarea autosize.

(function() {

    if ( typeof autosize !== 'function' ) return;

    const attachAutoSize = ( $context ) => {
        if ( typeof $context.querySelectorAll !== 'function' ) return;

        let $textareas = $context.querySelectorAll( 'textarea' );
        if ( !$textareas.length && $context.nodeName === 'TEXTAREA' )
            $textareas = [$context];

        [].forEach.call( $textareas, ( $textarea ) => {
            $textarea.style.resize = 'none';
            autosize( $textarea );
        } )
    }

    // on page load
    attachAutoSize( document.documentElement );

    // on ajax load
    new MutationObserver(function ( entries ) {
        entries.forEach( ( entry) => {
            entry.addedNodes.forEach( attachAutoSize );
        } );
    } ).observe( document.documentElement, {
        subtree: true,
        childList: true
    } );

})();

// ---
// Auto width of text input.

Behaviours.add( 'input:autowidth', $context => {

    [].forEach.call( $context.querySelectorAll( 'input.autowidth' ), $input => {
        $input.addEventListener( 'input', e => {
            e.target.style.width = `${e.target.value.split( '' ).length + 2}ch`;
        } )
    } )

} )
