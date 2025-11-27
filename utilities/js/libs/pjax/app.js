// ---
// Pjax. AJAX navigation.

(function() {

    if ( typeof Pjax === 'undefined' ) return;

    // overwrite `getElements` function to alter results
    Pjax.prototype.getElements = function( el ) {
        return Alter.do( 'pjax-elements', [].slice.call( el.querySelectorAll( this.options.elements ) )
            .filter( element => {
                switch ( element.tagName ) {
                    case 'A':
                        if ( element.classList.contains( 'no-pjax' ) || element.closest( '.no-pjax' ) ) return false;
                        if ( element.target === '_blank' ) return false;
                        if ( /\/wp-admin\//.test( element.href ) ) return false;
                        if ( element.closest( '.widget_polylang' ) || element.closest( '.bogo-language-switcher' ) ) return false;
                        if ( /\.(pdf|vcf|jpe?g|gif|png|webp|zip|mp4)$/.test( element.href ) ) return false;
                        break;

                    case 'FORM':
                        if ( element.classList.contains( 'wpcf7-form' ) ) return false;
                        break;
                }

                return true;
            } )
        )
    }

    // elements to be updated
    window.pjax = new Pjax( {
        // elements: 'a[href], form[action]',
        selectors: Alter.do( 'pjax-selectors', [
            'head title',
            '#core-block-supports-inline-css',
            '#masthead',
            '#primary',
            '#colophon',
            '#wpadminbar'
        ] ),
        switches: Alter.do( 'pjax-switches', {
            '#primary': function( oldEl, newEl, options ) {
                const $html = document.createElement( 'html' );
                $html.innerHTML = options.request.responseText;
                const $body = $html.querySelector( 'body' );

                // switch html attributes
                ['lang', 'dir', 'class'].forEach( attribute => {
                    let value = options.request.responseText.match( new RegExp( `<html.*${attribute}="([^"]*)"` ) );
                    value = value ? value[1] : '';

                    if ( value === '' ) {
                        return document.getElementsByTagName( 'html' )[0].removeAttribute( attribute );
                    }

                    switch ( attribute ) {
                        default:
                            document.getElementsByTagName( 'html' )[0].setAttribute( attribute, value );
                            break;

                        case 'class':
                            document.getElementsByTagName( 'html' )[0].className = value;
                            break;
                    }
                } );

                // switch body classes
                document.body.className = $body.className;

                oldEl.replaceWith( newEl );
                this.onSwitch();
            }
        } ),
        cacheBust: Alter.do( 'pjax-cacheBust', false ),
        currentUrlFullReload: Alter.do( 'pjax-currentUrlFullReload', false ),
        debug: Alter.do( 'pjax-debug', false ),
        history: Alter.do( 'pjax-history', true ),
        scrollRestoration: Alter.do( 'pjax-scrollRestoration', true ),
        scrollTo: Alter.do( 'pjax-scrollTo', false ),
    } );

    // get/create overlay for transition effect
    let $overlay = document.getElementById( 'pjax-transition' );
    if ( !$overlay ) {
        $overlay = document.createElement( 'div' );
        $overlay.id = 'pjax-transition';
        $overlay.style.display = 'none';
        document.body.appendChild( $overlay );
    }

    // get styles of `$overlay`
    const CSS= getComputedStyle( $overlay );
    // ... to extract `transition-duration` for `setTimeout` functions
    const pjaxTransitionDuration= parseFloat( CSS.getPropertyValue( 'transition-duration' ) ) * 1000;

    // delay for transition
    pjax._handleResponse = pjax.handleResponse;
    pjax.handleResponse = function( responseText, request, href, options ) {
        setTimeout( () => {
            pjax._handleResponse( responseText, request, href, options )
        }, pjaxTransitionDuration );
    }

    // Pjax request begins
    document.addEventListener('pjax:send', function( e ) {
        // fade out page (show overlay)
        $overlay.style.display = '';
        // for _some reason_ we need to wait 2 frames after _displaying_ the overlay
        // before start fading
        window.requestAnimationFrame( () => {
            window.requestAnimationFrame(
                () => document.body.setAttribute( 'data-pjax-transition', '' )
            );
        } );

        setTimeout( function () {
            // remove MediaElements aka video/audio
            if ( typeof mejs !== 'undefined' ) Object.keys( mejs.players || {} ).forEach( ( id ) => {
                const player = mejs.players[id];
                if ( !player.paused ) player.pause();
                player.options.stretching = 'auto'; // other will cause an js error on remove
                player.remove();
            } );

            // destroy all gsap ScrollTrigger
            if ( typeof ScrollTrigger !== 'undefined' )
                ScrollTrigger.killAll();

            // jump to top
            window.scrollTo({
                top: 0,
                behavior: 'instant'
            } )
        }, pjaxTransitionDuration );

    }, { passive: true } );

    // Pjax request finished
    document.addEventListener('pjax:complete', function( e ) {
        document.documentElement.style.overflow = '';

        // fade in page (hide overlay)
        window.requestAnimationFrame( () => {
            $overlay.style.pointerEvents = 'none';
            document.body.removeAttribute( 'data-pjax-transition' );

            setTimeout( () => {
                // next redirect is already in action
                if ( document.body.hasAttribute( 'data-pjax-transition' ) ) return;

                // prevent overlay to be rendered when it's not visible
                $overlay.style.display = 'none';

                $overlay.style.pointerEvents = '';
            }, pjaxTransitionDuration );
        } );
    }, { passive: true } );

    // Pjax request succeeds
    document.addEventListener( 'pjax:success', e => {
        // Matomo : tracking a new page view
        // https://developer.matomo.org/guides/spa-tracking#tracking-a-new-page-view
        (function() {
            var _paq = window._paq = window._paq || [];
            _paq.push( ['setCustomUrl', location.href.replace( location.origin, '' )] );
            _paq.push( ['setDocumentTitle', document.title]);
            _paq.push( ['trackPageView'] );
        })();

        // re-attach all behaviours
        window.requestAnimationFrame(
            () => Alter.do( 'pjax-selectors', ['#masthead', '#primary', '#colophon'] ).forEach( ( id ) => Behaviours.attach( document.querySelector( id ) ) )
        );
    }, { passive: true } );

    // Pjax request fails
    document.addEventListener('pjax:error', function( e ) {
        // smth. went wrong ... so load requested page the GET way
        // window.location = e.request.responseURL;
    }, { passive: true } );

})();