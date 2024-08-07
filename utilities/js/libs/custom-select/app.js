/**
 * Attach custom select js.
 */

(function() {

    if ( typeof customSelect !== 'function' ) return;

    const attachCustomSelect = $context => {
        if ( typeof $context.querySelectorAll !== 'function' ) return;
        if ( $context.closest( '.customSelect' ) ) return; // already attached

        let $selects = [];

        // no custom select selectors
        if ( typeof customSelectVars === 'undefined' || !customSelectVars.selectors ) {
            $selects = $context.querySelectorAll( 'select' );

            if ( !$selects.length && $context.nodeName === 'SELECT' ) {
                $selects = [$context];
            }
        }
        else customSelectVars.selectors.split( /\r?\n/ ).forEach( selector => {
            try {
                $context.querySelectorAll( selector.trim() ).forEach( $element => {
                    if ( $element.nodeName !== 'SELECT' ) return;
                    $selects.push( $element );
                } )
            } catch ( e ) {}
        } );

        $selects.forEach( $select => {
            $select.style.display = 'none';
            customSelect( $select );
        } );
    }

    // on page load
    attachCustomSelect( document.documentElement );

    // on ajax load
    new MutationObserver(function(entries) {
        entries.forEach( entry => {
            entry.addedNodes.forEach( attachCustomSelect );
        } );
    } ).observe (document.documentElement, {
        subtree: true,
        childList: true
    } );

})();