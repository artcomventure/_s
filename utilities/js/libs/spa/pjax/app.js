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
                        if ( element.classList.contains( 'wp-block-file__button' ) ) return false;
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

    // overlay for transition effect (printed in `../spa.php`, fallback: create)
    let $overlay = document.getElementById( 'spa-transition' );
    if ( !$overlay ) {
        $overlay = document.createElement( 'div' );
        $overlay.id = 'spa-transition';
        $overlay.style.display = 'none';
        document.body.appendChild( $overlay );
    }

    // get styles of `$overlay`
    const CSS= getComputedStyle( $overlay );
    // ... to extract `transition-duration` for `setTimeout` functions
    const pjaxTransitionDuration= parseFloat( CSS.getPropertyValue( 'transition-duration' ) ) * 1000;

    // latest `#primary` switch (a newer page load cancels a pending one)
    let switchId = 0;

    // add missing styles of the new page (`id` mandatory, block styles are loaded per page), existing ones stay;
    // update changed inline styles; head styles before the theme's stylesheet, footer styles at the end of `<body>`.
    // Resolves when new stylesheets are loaded (max. 3 s).
    function syncStyles( $document, selectors ) {
        const $themeStyle = document.getElementById( Alter.do( 'pjax-theme-style', '_s-style-css' ) );
        const loads = [];

        $document.querySelectorAll( 'link[rel="stylesheet"][id], style[id]' ).forEach( $style => {
            // switched anyway
            if ( $style.closest( selectors.join( ',' ) ) ) return;

            const $current = document.getElementById( $style.id );

            if ( !$current ) {
                const $new = document.importNode( $style, true );
                if ( $new.tagName === 'LINK' ) loads.push( new Promise( resolve => {
                    $new.addEventListener( 'load', resolve, { once: true } );
                    $new.addEventListener( 'error', resolve, { once: true } );
                } ) );

                if ( $style.closest( 'head' ) ) document.head.insertBefore( $new, $themeStyle?.parentNode === document.head ? $themeStyle : null );
                else document.body.appendChild( $new );
            } else if ( $style.tagName === 'STYLE' && $current.textContent !== $style.textContent ) {
                $current.textContent = $style.textContent;
            }
        } );

        return Promise.race( [Promise.all( loads ), new Promise( resolve => setTimeout( resolve, 3000 ) )] );
    }

    // instantiate after all deferred scripts ran: their `Alter.add( 'pjax-…' )` calls are registered
    // (deferred scripts run while `readyState` is already `interactive`, before `DOMContentLoaded`)
    if ( document.readyState === 'complete' ) init();
    else document.addEventListener( 'DOMContentLoaded', init );

    function init() {
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
                    // inert document: no image loading or inline handlers (unlike innerHTML in the live document)
                    const $document = new DOMParser().parseFromString( options.request.responseText, 'text/html' );
                    const $body = $document.body;
                    const id = ++switchId;

                    // switch once new stylesheets are loaded (behind the overlay)
                    syncStyles( $document, this.options.selectors ).then( () => {
                        // a newer page is loading: its switches take over
                        if ( id !== switchId ) return;

                        // switch html attributes
                        ['lang', 'dir', 'class'].forEach( attribute => {
                            const value = $document.documentElement.getAttribute( attribute ) || '';

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
                    } );
                }
            } ),
            cacheBust: Alter.do( 'pjax-cacheBust', false ),
            currentUrlFullReload: Alter.do( 'pjax-currentUrlFullReload', false ),
            debug: Alter.do( 'pjax-debug', false ),
            history: Alter.do( 'pjax-history', true ),
            scrollRestoration: Alter.do( 'pjax-scrollRestoration', true ),
            scrollTo: Alter.do( 'pjax-scrollTo', false ),
        } );

        // delay for transition
        pjax._handleResponse = pjax.handleResponse;
        pjax.handleResponse = function( responseText, request, href, options ) {
            setTimeout( () => {
                pjax._handleResponse( responseText, request, href, options )
            }, pjaxTransitionDuration );
        }
    }

    // Pjax request begins
    document.addEventListener('pjax:send', function( e ) {
        // fade out page (show overlay)
        $overlay.style.display = '';
        // for _some reason_ we need to wait 2 frames after _displaying_ the overlay
        // before start fading
        window.requestAnimationFrame( () => {
            window.requestAnimationFrame(
                () => document.body.setAttribute( 'data-spa-transition', '' )
            );
        } );

        setTimeout( function () {
            // remove MediaElements aka video/audio
            if ( typeof mejs !== 'undefined' ) Object.keys( mejs.players || {} ).forEach( ( id ) => {
                const player = mejs.players[id];
                // `container` may be a jQuery object
                const $container = player.container?.jquery ? player.container[0] : player.container;
                const $media = $container ? [...$container.querySelectorAll( 'video, audio' )] : [];

                if ( !player.paused ) player.pause();
                player.remove();

                // stop loading, otherwise later media events hit the removed player
                $media.forEach( $element => {
                    $element.removeAttribute( 'src' );
                    $element.querySelectorAll( 'source' ).forEach( $source => $source.remove() );
                    $element.load();
                } );
            } );

            // video.js (stops loading and removes the element)
            if ( typeof videojs !== 'undefined' ) Object.values( videojs.getPlayers() ).forEach( player => player && !player.isDisposed() && player.dispose() );

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
            document.body.removeAttribute( 'data-spa-transition' );

            setTimeout( () => {
                // next redirect is already in action
                if ( document.body.hasAttribute( 'data-spa-transition' ) ) return;

                // prevent overlay to be rendered when it's not visible
                $overlay.style.display = 'none';

                $overlay.style.pointerEvents = '';
            }, pjaxTransitionDuration );
        } );
    }, { passive: true } );

    // target of `location.hash` (`/page/#section`), if any
    function getHashTarget() {
        const hash = location.hash.slice( 1 );
        if ( !hash ) return null;

        try {
            return document.getElementById( decodeURIComponent( hash ) );
        } catch {
            // malformed escape sequence
            return document.getElementById( hash );
        }
    }

    // referrer for tracking
    let previousUrl = location.href;

    // Pjax request succeeds
    document.addEventListener( 'pjax:success', e => {
        // Matomo : tracking a new page view
        // https://developer.matomo.org/guides/spa-tracking#tracking-a-new-page-view
        (function() {
            var _paq = window._paq = window._paq || [];
            _paq.push( ['setReferrerUrl', previousUrl] );
            _paq.push( ['setCustomUrl', location.href.replace( location.origin, '' )] );
            _paq.push( ['setDocumentTitle', document.title]);
            _paq.push( ['trackPageView'] );
            previousUrl = location.href;
        })();

        // move focus to new content, so screen readers announce it and next tab continues there
        // (not on back/forward, where the scroll position is restored; `[autofocus]` wins)
        if ( !e.backward && !e.forward && !document.activeElement?.hasAttribute( 'autofocus' ) ) {
            const $primary = document.querySelector( '#primary' );
            const $target = Alter.do( 'pjax-focus', getHashTarget() || $primary?.querySelector( 'h1' ) || $primary );

            if ( $target ) {
                if ( !$target.matches( 'a[href], button, input, select, textarea, [tabindex]' ) ) $target.setAttribute( 'tabindex', -1 );
                $target.focus( { preventScroll: true } );
            }
        }

        // re-attach all behaviours
        window.requestAnimationFrame(
            () => Alter.do( 'pjax-selectors', ['#masthead', '#primary', '#colophon'] ).forEach( ( selector ) => {
                // missing element would attach all behaviours to `document` again
                const $element = document.querySelector( selector );
                if ( $element ) Behaviours.attach( $element );
            } )
        );
    }, { passive: true } );

    // Pjax request fails
    document.addEventListener('pjax:error', function( e ) {
        // smth. went wrong ... so load requested page the GET way
        // window.location = e.request.responseURL;
    }, { passive: true } );

})();