(function() {

    const $info = document.getElementById( 'dev-info-panel' );
    if ( !$info ) return;

    if ( !navigator.clipboard ) return;

    $info.classList.add( 'copyable' );

    $info.addEventListener( 'click', e => {
        const data = [];

        [].forEach.call( $info.querySelectorAll( '[data-copy]' ), $data => {
            data.push( `${$data.getAttribute( 'data-copy' ) || $data.title}: ${$data.innerText}` )
        } )

        navigator.clipboard.writeText( data.join( "\n" ) )
            .then( () => alert( wp.i18n.__( 'Info copied to clipboard and ready to paste.', 'dev' ) ) );
    }, { passive: true } )

})()