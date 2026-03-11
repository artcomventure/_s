(function() {

    // listen to mail-sent event and redirect
    document.addEventListener( 'wpcf7mailsent', function( e ) {
        if ( typeof wpcf7MailSentOkRedirects === 'undefined' ) return;

        if ( !!wpcf7MailSentOkRedirects[e.detail.contactFormId] ) {
            if ( typeof pjax !== 'undefined' )
                return pjax.loadUrl( wpcf7MailSentOkRedirects[e.detail.contactFormId] );

            location = wpcf7MailSentOkRedirects[e.detail.contactFormId];
        }
    }, false );

    function markFormAsRedirectOnMailSentOk( $context ) {
        if ( typeof wpcf7MailSentOkRedirects === 'undefined' ) return;

        // mark form as `redirect-on-mail-sent-ok`
        for ( const form_id in wpcf7MailSentOkRedirects ) {
            if ( !wpcf7MailSentOkRedirects.hasOwnProperty( form_id ) ) continue;

            let $form = $context.querySelector( 'input[name="_wpcf7"][value="' + form_id + '"]' );
            if ( !$form ) continue;

            while ( $form && $form.tagName !== 'FORM' )
                $form = $form.parentElement

            if ( !$form ) continue;

            $form.classList.add( 'redirect-on-mail-sent-ok' );
        }
    }

    Behaviours.add( 'form:wpcf7:mfaromso', markFormAsRedirectOnMailSentOk )

})();
