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

    // Initially, `wpcf7.init` is called immediately by contact form 7 plugin js.
    // But due to Pjax we need to attach js after every "page load".
    // To not interfere with the initial call we register this Behaviour after initial attach.
    Behaviours.add( 'form:wpcf7:init', $context => {
        Behaviours.remove( 'form:wpcf7:init' );

        if ( typeof wpcf7 === 'undefined' ) return;

        markFormAsRedirectOnMailSentOk( $context )

        Behaviours.add( 'form:wpcf7:init', $context => {
            $context.querySelectorAll('.wpcf7 > form' ).forEach( wpcf7.init )
            markFormAsRedirectOnMailSentOk( $context )
        } )
    } )

})();
