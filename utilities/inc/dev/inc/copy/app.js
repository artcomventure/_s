(function() {

    const $info = document.getElementById( 'dev-info-panel' );
    if ( !$info ) return;

    if ( !navigator.clipboard ) return;

    $info.classList.add( 'copyable' );

    $info.addEventListener( 'click', e => {
        // links (e.g. repository) navigate, don't copy
        if ( e.target.closest( 'a' ) ) return;

        const data = [];

        [].forEach.call( $info.querySelectorAll( '[data-copy]' ), $data => {
            data.push( `${$data.getAttribute( 'data-copy' ) || $data.title}: ${$data.innerText}`.replace( "\n", ' ' ) )
        } )

        navigator.clipboard.writeText( data.join( "\n" ) )
            .then( () => alert( wp.i18n.__( 'Info copied to clipboard and ready to paste.', 'dev' ) ) );
    }, { passive: true } )

})()