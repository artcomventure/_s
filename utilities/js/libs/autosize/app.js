(function() {

    // `autosize.js` checks horizontal resize only :/
    // not enough with our `fluid` calculations
    const resizeObserver = new ResizeObserver( entries => {
        entries.forEach( entry => {
            // check if element is connected to the `document`
            if ( !entry.target.parentElement )
                return resizeObserver.unobserve( entry.target );

            autosize.update( entry.target );
        } )
    } )

    // attach textarea autosize
    const attachAutoHeight = ( $context ) => {
        if ( typeof autosize !== 'function' ) return;

        $context = $context || document;
        if ( typeof $context.querySelectorAll !== 'function' ) return;

        let $textareas = $context.querySelectorAll( 'textarea' );
        if ( !$textareas.length && $context.nodeName === 'TEXTAREA' )
            $textareas = [$context];

        [].forEach.call( $textareas, $textarea => {
            $textarea.style.resize = 'none';
            autosize( $textarea );
            resizeObserver.observe( $textarea );
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
    attachAutoHeight() && attachAutoWidth();

    // on ajax load
    new MutationObserver(function ( entries ) {
        entries.forEach( ( entry) => {
            entry.addedNodes.forEach( attachAutoHeight );
            entry.addedNodes.forEach( attachAutoWidth );
        } );
    } ).observe( document.documentElement, {
        subtree: true,
        childList: true
    } );

})();
