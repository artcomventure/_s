(function() {

    // listen to mail-sent event (dispatched on the form) and redirect
    document.addEventListener( 'wpcf7mailsent', function( e ) {
        const redirect = e.target.dataset?.redirect;
        if ( !redirect ) return;

        if ( typeof pjax !== 'undefined' ) return pjax.loadUrl( redirect );
        location = redirect;
    }, false );

})();
