(function() {

    const $info = document.getElementById( 'dev-info-panel' );
    if ( !$info ) return;

    $info.addEventListener( 'contextmenu', e => {
        e.preventDefault();

        $info.classList.toggle( 'show-all' );
    } )

})()