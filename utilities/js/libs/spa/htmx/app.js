// ---
// htmx (2.x). AJAX navigation: boost internal links, swap `#masthead`, `#primary`, `#colophon` and `#wpadminbar`
// (`Alter.add( 'htmx-selectors', … )`, IDs only) out of band – scripts at the end of `<body>` stay untouched.

(function() {

    if ( typeof htmx === 'undefined' ) return;

    // elements to be swapped (lazy: all deferred scripts registered their `Alter.add()` calls), missing ones are skipped
    let selectors;
    const getSelectors = () => selectors ??= Alter.do( 'htmx-selectors', ['#masthead', '#primary', '#colophon', '#wpadminbar'] ).join( ',' );

    // links which are excluded from boosting
    function shouldBoost( $link ) {
        if ( $link.closest( '.no-spa' ) ) return false;
        if ( $link.target && $link.target !== '_self' ) return false;
        if ( $link.hasAttribute( 'download' ) ) return false;

        let url;
        try {
            url = new URL( $link.href, location.href );
        } catch {
            return false;
        }

        // external links (also `mailto:`, `tel:` …)
        if ( url.origin !== location.origin ) return false;
        // anchor on the current page
        if ( url.hash && url.pathname === location.pathname && url.search === location.search ) return false;
        if ( /\/wp-(admin|login\.php)|\/feed\/?$|\/xmlrpc\.php/.test( url.pathname ) ) return false;
        if ( /\.(pdf|vcf|ics|jpe?g|gif|png|webp|avif|svg|zip|mp3|mp4|docx?|xlsx?|pptx?)$/i.test( url.pathname ) ) return false;
        // language switcher: full reload intended
        if ( $link.hasAttribute( 'hreflang' ) || $link.closest( '.widget_polylang, .bogo-language-switcher' ) ) return false;
        if ( $link.classList.contains( 'wp-block-file__button' ) ) return false;

        return Alter.do( 'htmx-boost', true, $link );
    }

    // preload boosted links (extension `ext-preload.min.js`), opt-in via `Alter.add( 'htmx-preload', () => true )`:
    // `true`/`'mousedown'` (on press, ~100 ms gain) or `'mouseover'` (after 100 ms hover, more requests);
    // not for logged-in users (no-cache headers, preloads would be wasted)
    let preload;
    const getPreload = () => preload ??= document.body.classList.contains( 'logged-in' ) ? false : Alter.do( 'htmx-preload', false );

    // boost all links (inherited by `<body>`), then decide per link right before htmx initializes it
    document.body.setAttribute( 'hx-boost', 'true' );
    document.body.setAttribute( 'hx-ext', [document.body.getAttribute( 'hx-ext' ), 'preload'].filter( Boolean ).join( ',' ) );

    document.addEventListener( 'htmx:beforeProcessNode', e => {
        const $elt = e.target;

        // explicit setting wins
        if ( $elt.hasAttribute( 'hx-boost' ) || $elt.hasAttribute( 'data-hx-boost' ) ) return;

        // links only
        if ( $elt.tagName === 'FORM' ) return $elt.setAttribute( 'hx-boost', 'false' );
        if ( $elt.tagName !== 'A' ) return;

        if ( !shouldBoost( $elt ) ) return $elt.setAttribute( 'hx-boost', 'false' );

        $elt.setAttribute( 'hx-boost', 'true' );
        $elt.setAttribute( 'hx-swap', 'none' );
        $elt.setAttribute( 'hx-select-oob', getSelectors() );

        const trigger = getPreload();
        if ( trigger && !$elt.hasAttribute( 'preload' ) ) $elt.setAttribute( 'preload', trigger === true ? 'mousedown' : trigger );
    } );

    // content added by other scripts
    Behaviours.add( 'htmx-process', $context => $context !== document && htmx.process( $context ) );

    // ---
    // Document: attributes of `<html>`/`<body>` and styles outside the swapped elements.

    function syncAttributes( $element, attributes ) {
        Object.entries( attributes ).forEach( ( [name, value] ) => {
            if ( value === null ) $element.removeAttribute( name );
            else $element.setAttribute( name, value );
        } );
    }

    const getAttributes = ( $element, names ) => Object.fromEntries( names.map( name => [name, $element.getAttribute( name )] ) );

    // add missing styles (`id` mandatory) and update changed inline styles (e.g. `#core-block-supports-inline-css`),
    // existing ones stay; head styles before the theme's stylesheet, footer styles at the end of `<body>`.
    // Resolves when new stylesheets are loaded (max. 3 s).
    function syncStyles( $document ) {
        const $themeStyle = document.getElementById( Alter.do( 'htmx-theme-style', '_s-style-css' ) );
        const loads = [];

        $document.querySelectorAll( 'link[rel="stylesheet"][id], style[id]' ).forEach( $style => {
            // swapped anyway
            if ( $style.closest( getSelectors() ) ) return;

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

    // remove MediaElements aka video/audio
    function removeMediaPlayers() {
        if ( typeof mejs !== 'undefined' ) Object.keys( mejs.players || {} ).forEach( id => {
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
    }

    // players' markup → original media element (cached pages: the players can't be restored from markup)
    function unwrapMediaPlayers( $root ) {
        // video.js: attributes and classes moved to the wrapper
        $root.querySelectorAll( 'div.video-js' ).forEach( $player => {
            const $media = $player.querySelector( '.vjs-tech' );
            if ( !$media ) return $player.remove();

            $media.id = $player.id;
            $media.className = [...$player.classList].filter( name => name !== 'video-js' && !name.startsWith( 'vjs-' ) && !name.endsWith( '-dimensions' ) ).join( ' ' );
            ['src', 'poster', 'preload', 'width', 'height', 'loop', 'muted', 'autoplay', 'playsinline'].forEach( name => {
                if ( $player.hasAttribute( name ) && !$media.hasAttribute( name ) ) $media.setAttribute( name, $player.getAttribute( name ) );
            } );
            $media.removeAttribute( 'tabindex' );
            $media.setAttribute( 'controls', '' );

            $player.replaceWith( $media );
        } );

        // mediaelementjs
        $root.querySelectorAll( '.mejs-container' ).forEach( $container => {
            const $wrapper = $container.querySelector( 'mediaelementwrapper' );
            const $media = $wrapper?.querySelector( 'video, audio' );
            if ( !$media ) return $container.remove();

            // generated id (`mejs_…`) or original one (`ID_html5` → `ID`)
            if ( $wrapper.id.startsWith( 'mejs_' ) ) $media.removeAttribute( 'id' );
            else $media.id = $wrapper.id;
            $media.removeAttribute( 'style' );
            $media.setAttribute( 'controls', '' );

            if ( $container.previousElementSibling?.classList.contains( 'mejs-offscreen' ) ) $container.previousElementSibling.remove();
            $container.replaceWith( $media );
        } );
    }

    // new stylesheets of the swapped page loaded
    let stylesLoaded = Promise.resolve();

    function beforePageSwap( $document ) {
        stylesLoaded = syncStyles( $document );
        removeMediaPlayers();

        // destroy all gsap ScrollTrigger
        if ( typeof ScrollTrigger !== 'undefined' ) ScrollTrigger.killAll();
    }

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

    function afterPageSwap( restore = false ) {
        // Matomo : tracking a new page view
        // https://developer.matomo.org/guides/spa-tracking#tracking-a-new-page-view
        const _paq = window._paq = window._paq || [];
        _paq.push( ['setReferrerUrl', previousUrl] );
        _paq.push( ['setCustomUrl', location.href.replace( location.origin, '' )] );
        _paq.push( ['setDocumentTitle', document.title] );
        _paq.push( ['trackPageView'] );
        previousUrl = location.href;

        // Google Analytics (gtag.js) : tracking a new page view, opt-in via `Alter.add( 'htmx-gtag', () => true )`
        // (by default GA4's enhanced measurement tracks history changes, see `htmx:beforeHistoryUpdate`)
        // !!! when enabled, disable "page changes based on browser history events" in GA4, otherwise views count twice !!!
        // https://developers.google.com/analytics/devguides/collection/ga4/single-page-applications
        if ( typeof window.gtag === 'function' && Alter.do( 'htmx-gtag', false ) ) {
            window.gtag( 'event', 'page_view', {
                page_location: location.href,
                page_title: document.title,
            } );
        }

        // re-attach behaviours to the swapped elements
        requestAnimationFrame( () => document.querySelectorAll( getSelectors() ).forEach( $element => Behaviours.attach( $element ) ) );

        // move focus to new content, so screen readers announce it and next tab continues there
        // (not on history restore, `[autofocus]` wins)
        if ( !restore && !document.activeElement?.hasAttribute( 'autofocus' ) ) {
            const $primary = document.querySelector( '#primary' );
            const $target = Alter.do( 'htmx-focus', getHashTarget() || $primary?.querySelector( 'h1' ) || $primary );

            if ( $target ) {
                if ( !$target.matches( 'a[href], button, input, select, textarea, [tabindex]' ) ) $target.setAttribute( 'tabindex', -1 );
                $target.focus( { preventScroll: true } );
            }
        }
    }

    // ---
    // Transition: overlay shared with pjax (`#spa-transition` printed in `../spa.php`, `body[data-spa-transition]`, `../style.scss`).

    // fallback, if not printed
    let $overlay = document.getElementById( 'spa-transition' );
    if ( !$overlay ) {
        $overlay = document.createElement( 'div' );
        $overlay.id = 'spa-transition';
        $overlay.style.display = 'none';
        document.body.appendChild( $overlay );
    }

    const getTransitionDuration = () => ( parseFloat( getComputedStyle( $overlay ).transitionDuration ) || 0 ) * 1000;

    let transitionActive = false;
    // current transition (an older page mustn't end a newer one)
    let transitionId = 0;
    let fadeStart = null;

    function startTransition() {
        transitionActive = true;
        transitionId++;
        fadeStart = null;

        // fade out page (show overlay)
        $overlay.style.display = '';
        $overlay.style.pointerEvents = '';
        // for _some reason_ we need to wait 2 frames after _displaying_ the overlay before start fading
        requestAnimationFrame( () => requestAnimationFrame( () => {
            if ( !transitionActive ) return;
            fadeStart = performance.now();
            document.body.setAttribute( 'data-spa-transition', '' );
        } ) );
    }

    // time until the overlay is fully visible
    function getRemainingTransition() {
        if ( !transitionActive ) return 0;
        if ( fadeStart === null ) return getTransitionDuration();
        return Math.max( 0, getTransitionDuration() - ( performance.now() - fadeStart ) );
    }

    function endTransition() {
        if ( !transitionActive ) return;
        transitionActive = false;

        document.documentElement.style.overflow = '';

        // fade in page (hide overlay)
        requestAnimationFrame( () => {
            $overlay.style.pointerEvents = 'none';
            document.body.removeAttribute( 'data-spa-transition' );

            setTimeout( () => {
                // next transition is already in action
                if ( transitionActive ) return;

                // prevent overlay to be rendered when it's not visible
                $overlay.style.display = 'none';
                $overlay.style.pointerEvents = '';
            }, getTransitionDuration() );
        } );
    }

    // ---
    // Navigation (boosted link) and history restore from server.

    let pageSwap = null;
    const isHistoryRequest = detail => !!detail.requestConfig?.headers?.['HX-History-Restore-Request'];

    // preload requests are boosted too, but cancelled by the extension (after the document listeners)
    const isPreloadRequest = detail => detail.requestConfig?.headers?.['HX-Preloaded'] === 'true';

    const isPageRequest = detail => ( detail.boosted || isHistoryRequest( detail ) ) && !isPreloadRequest( detail );

    document.addEventListener( 'htmx:beforeRequest', e => isPageRequest( e.detail ) && startTransition() );

    document.addEventListener( 'htmx:beforeSwap', e => {
        if ( e.target !== document.body || !isPageRequest( e.detail ) ) return;
        if ( !e.detail.shouldSwap ) return;

        pageSwap = {
            restore: isHistoryRequest( e.detail ),
            // inert document: no image loading or script execution
            document: new DOMParser().parseFromString( e.detail.serverResponse, 'text/html' ),
        };

        // swap when the overlay is fully visible
        const delay = Math.ceil( getRemainingTransition() );
        if ( !delay ) return beforePageSwap( pageSwap.document );

        e.detail.swapOverride = `none swap:${delay}ms`;
        // runs right before htmx' swap (timers with the same delay run in order)
        const $document = pageSwap.document;
        setTimeout( () => beforePageSwap( $document ), delay );
    } );

    // no swap (e.g. `204`)
    document.addEventListener( 'htmx:afterRequest', e => isPageRequest( e.detail ) && !pageSwap && endTransition() );

    // htmx pushes the URL before the swap and sets the title after it: set the new title first,
    // so history listeners (e.g. GA4's enhanced measurement) get URL and title of the same page
    // (the history snapshot of the previous page is taken already)
    document.addEventListener( 'htmx:beforeHistoryUpdate', () => {
        const title = pageSwap?.document.title;
        if ( title ) document.title = title;
    } );

    document.addEventListener( 'htmx:afterSwap', e => {
        if ( e.target !== document.body || !pageSwap ) return;

        // not before: htmx takes the history snapshot of the current page after `htmx:beforeSwap`
        syncAttributes( document.documentElement, getAttributes( pageSwap.document.documentElement, ['lang', 'dir', 'class'] ) );
        syncAttributes( document.body, getAttributes( pageSwap.document.body, ['class'] ) );

        afterPageSwap( pageSwap.restore );
        pageSwap = null;
    } );

    // fade in new page after htmx scrolled to top (`show:top`) and its new stylesheets are loaded
    document.addEventListener( 'htmx:afterSettle', e => {
        if ( e.target !== document.body ) return;

        const id = transitionId;
        stylesLoaded.then( () => id === transitionId && endTransition() );
    } );

    // ---
    // History: htmx caches and restores the whole `<body>` (incl. scripts), so restore the swapped elements only.

    // htmx sets this after its own restore (otherwise the next snapshot is cached under the previous path)
    function setCurrentPathForHistory( path ) {
        try {
            sessionStorage.setItem( 'htmx-current-path-for-history', path );
        } catch {
            // no storage, no cache
        }
    }

    // remember document attributes with the cached page
    document.addEventListener( 'htmx:historyItemCreated', e => {
        e.detail.item.html = getAttributes( document.documentElement, ['lang', 'dir', 'class'] );
        e.detail.item.body = getAttributes( document.body, ['class'] );

        // inert (no image loading or script execution)
        if ( /mejs-container|video-js/.test( e.detail.item.content ) ) {
            const $template = document.createElement( 'template' );
            $template.innerHTML = e.detail.item.content;
            unwrapMediaPlayers( $template.content );
            e.detail.item.content = $template.innerHTML;
        }
    } );

    document.addEventListener( 'htmx:historyCacheHit', e => {
        e.preventDefault();

        const { item, path } = e.detail;
        setCurrentPathForHistory( path );
        if ( item.html ) syncAttributes( document.documentElement, item.html );
        if ( item.body ) syncAttributes( document.body, item.body );

        // the leaving page's players (cache miss: `beforePageSwap()`)
        removeMediaPlayers();

        htmx.swap( document.body, item.content, { swapStyle: 'none', swapDelay: 0, settleDelay: 0 }, {
            selectOOB: getSelectors(),
            title: item.title,
            contextElement: document.body,
        } );
        window.scrollTo( { top: item.scroll, behavior: 'instant' } );

        afterPageSwap( true );
        htmx.trigger( document.body, 'htmx:historyRestore', { path, item } );
    } );

    document.addEventListener( 'htmx:historyCacheMiss', e => {
        e.preventDefault();
        setCurrentPathForHistory( e.detail.path );

        htmx.ajax( 'GET', e.detail.path, {
            source: document.body,
            swap: 'none',
            selectOOB: getSelectors(),
            headers: { 'HX-History-Restore-Request': 'true' },
        } );
    } );

    // smth. went wrong ... so load requested page the GET way
    ['htmx:responseError', 'htmx:sendError'].forEach( event => document.addEventListener( event, e => {
        if ( e.detail.boosted && e.detail.elt?.href ) location.href = e.detail.elt.href;
    } ) );

})();
