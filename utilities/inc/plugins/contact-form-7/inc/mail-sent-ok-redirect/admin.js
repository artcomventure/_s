(function( window, document, $, undefined ) {

    $( '#wpcf7-message-mail-sent-ok' )
        .suggest( ajaxurl + '?action=suggest-link-ajax&page=1' )
        .after( '<br />', `<span>${wp.i18n.__( 'If you enter a URL, the user will be redirected to it.', 'cf7' )}</span>` );

})( window, document, jQuery );
