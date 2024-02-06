// ---
// Attach custom select.

(function() {

    if ( typeof customSelect !== 'function' ) return;

    const attachCustomSelect = ( $context ) => {
        if ( typeof $context.querySelectorAll !== 'function' ) return;
        if ( $context.closest( '.customSelect' ) ) return;

        let $selects = $context.querySelectorAll( 'select' );
        if ( !$selects.length && $context.nodeName === 'SELECT' )
            $selects = [$context];

        [].forEach.call( $selects, ( $select ) => {
            $select.style.display = 'none';
            customSelect( $select );
        } )
    }

    // on page load
    attachCustomSelect( document.documentElement );

    // on ajax load
    new MutationObserver(function ( entries ) {
        entries.forEach( ( entry) => {
            entry.addedNodes.forEach( attachCustomSelect );
        } );
    } ).observe( document.documentElement, {
        subtree: true,
        childList: true
    } );

})();