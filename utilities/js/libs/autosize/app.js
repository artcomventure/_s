(function() {

    // attach textarea autosize
    const attachAutosize = ( $context ) => {
        if ( typeof autosize !== 'function' ) return;

        $context = $context || document;

        let $textareas = $context.querySelectorAll( 'textarea' );
        if ( !$textareas.length && $context.nodeName === 'TEXTAREA' )
            $textareas = [$context];

        [].forEach.call( $textareas, $textarea => {
            $textarea.style.resize = 'none';
            autosize( $textarea );
        } )
    }

    // auto width of text input
    const attachAutoWidth = ( $context ) => {
        $context = $context || document;

        if ( typeof $context.querySelectorAll !== 'function' ) return;

        let $inputs = $context.querySelectorAll( 'input.autowidth' );
        if ( !$inputs.length && $context.nodeName === 'INPUT' && $context.classList.contains( 'autowidth' ) )
            $inputs = [$context];

        [].forEach.call( $inputs, $input => {
            $input.addEventListener( 'input', e => {
                e.target.style.width = `${e.target.value.split( '' ).length + 2}ch`;
            } )
        } )
    }

    // on page load
    attachAutosize() && attachAutoWidth();

    // on ajax load
    new MutationObserver(function ( entries ) {
        entries.forEach( ( entry) => {
            entry.addedNodes.forEach( attachAutosize );
            entry.addedNodes.forEach( attachAutoWidth );
        } );
    } ).observe( document.documentElement, {
        subtree: true,
        childList: true
    } );

})();
