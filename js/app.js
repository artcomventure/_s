// Initially `doSliders` is called immediately by slider plugin's js.
// But due to Pjax we need to attach swiper js after every "page load".
// To not interfere with the initial call we register _slider Behaviour_ after initial attach.
Behaviours.add( 'media:sliders', () => {
    Behaviours.remove( 'media:sliders' );

    // make sure swiper js is loaded
    if ( typeof doSliders !== 'function' ) return;
    Behaviours.add( 'media:sliders', doSliders );
} );

// Close CCM widget on privacy policy page.
Behaviours.add( 'gdpr:privacy-policy', $context => {

    if ( !document.body.classList.contains( 'privacy-policy' ) ) return;
    if ( typeof CCM === 'undefined' ) return;

    CCM.closeWidget()

} );

(function() {

    // Called once a slider is initialized.
    // Do any slider adjustment here!
    const sliderAfterInit = swiper => {
        // responsive `slidesPerView`
        // @since 1.20.5
        (function() {
            const updateSlidesPerView = () => {
                if ( swiper.el.classList.contains( 'static-slidesPerView' ) ) return;

                let originalSlidesPerView = swiper.originalParams.slidesPerView === 'auto'
                    ? swiper.slides.length : swiper.originalParams.slidesPerView;

                let slidesPerView = originalSlidesPerView;
                ['xxs', 'xs', 'sm', 'md', 'lg', 'xl', 'xxl'].some( ( size, i) => {
                    if ( !BREAKPOINTS.down( size ) ) return;
                    slidesPerView = i + 1;
                    return true;
                } );

                if ( swiper.params.slidesPerView !== 'auto' ) {
                    slidesPerView = Math.min( originalSlidesPerView, slidesPerView );
                    if ( swiper.originalParams.slidesPerView > 1 && slidesPerView !== swiper.params.slidesPerView ) {
                        swiper.params.slidesPerView = slidesPerView;
                        swiper.params.slidesPerGroup = swiper.params.slidesPerView;
                    }
                }

                swiper.update();
            }

            // setting `swiper.params.breakpoints` doesn't work properly
            // ... so we use our custom calculation on swiper's resize event
            swiper.on( 'resize', updateSlidesPerView );
            updateSlidesPerView();
        })();
    };

    window.addEventListener( 'swiper:afterInit', e =>
        sliderAfterInit( e.detail ), { passive: true } );

    // sliders initialized before this (deferred) script ran
    document.querySelectorAll( '.swiper' ).forEach( $swiper =>
        $swiper.swiper?.initialized && sliderAfterInit( $swiper.swiper ) );

})();

// This event is triggered once a slider is initialized.
// Do any slider adjustment here!
window.addEventListener( 'swiper:afterInit', e => {
    const swiper = e.detail;

    // responsive `slidesPerView`
    // @since 1.20.5
    (function() {
        const updateSlidesPerView = () => {
            if ( swiper.el.classList.contains( 'static-slidesPerView' ) ) return;

            let originalSlidesPerView = swiper.originalParams.slidesPerView === 'auto'
                ? swiper.slides.length : swiper.originalParams.slidesPerView;

            let slidesPerView = originalSlidesPerView;
            ['xxs', 'xs', 'sm', 'md', 'lg', 'xl', 'xxl'].some( ( size, i) => {
                if ( !BREAKPOINTS.down( size ) ) return;
                slidesPerView = i + 1;
                return true;
            } );

            if ( swiper.params.slidesPerView !== 'auto' ) {
                slidesPerView = Math.min( originalSlidesPerView, slidesPerView );
                if ( swiper.originalParams.slidesPerView > 1 && slidesPerView !== swiper.params.slidesPerView ) {
                    swiper.params.slidesPerView = slidesPerView;
                    swiper.params.slidesPerGroup = swiper.params.slidesPerView;
                }
            }

            swiper.update();
        }

        // setting `swiper.params.breakpoints` doesn't work properly
        // ... so we use our custom calculation on swiper's resize event
        swiper.on( 'resize', updateSlidesPerView );
        updateSlidesPerView();
    })();

    // fluid `spaceBetween`
    // @since 1.20.5
    if ( swiper.params.spaceBetween ) {
        const updateSpaceBetween = () => {
            // progress from mobile to desktop
            const progress = Math.max( 0, Math.min( 1, (window.innerWidth - BREAKPOINTS.mobile) / (BREAKPOINTS.desktop - BREAKPOINTS.mobile) ) );
            // calculate space between
            swiper.params.spaceBetween = Math.max(8, swiper.originalParams.spaceBetween * progress )

            swiper.update()
        }

        // swiper's resize event: ends with the swiper (an observer on `<html>` would outlive pjax navigation)
        swiper.on( 'resize', updateSpaceBetween );
        updateSpaceBetween();
    }
}, { passive: true } );

/**
 * Toggle header.
 */
(function() {

    const threshold = 11 // min distance to be scrolled before making actions
    let $masthead, mastheadHeight = 0;

    // cache height: reading `offsetHeight` on scroll forces a style/layout calculation
    const mastheadObserver = new ResizeObserver( ( [entry] ) => {
        // check if element is connected to the `document`
        if ( !entry.target.isConnected ) return mastheadObserver.unobserve( entry.target );

        mastheadHeight = entry.borderBoxSize[0].blockSize;
    } );

    Behaviours.add( 'header:toggle', $context => {

        let $primary = $context.querySelector( '#primary' )
        if ( !$primary && $context?.id === 'primary' )
            $primary = $context;

        if ( $primary ) {
            if ( ($masthead && $masthead.parentElement ) || ($masthead = document.getElementById( 'masthead' )) ) {
                $masthead.addEventListener( 'mouseover', e => $masthead.classList.add( 'is-visible' ) )

                mastheadObserver.disconnect();
                mastheadObserver.observe( $masthead );
            }
        }
        // else $masthead = undefined

    } )

    let lastScrollY = 0;
    window.addEventListener('scroll', e => {
        if ( Math.abs( window.scrollY - lastScrollY ) < threshold ) return

        if ( $masthead ) {
            // `toggle()` with force only changes classes if needed
            $masthead.classList.toggle( 'is-togglable', window.scrollY >= mastheadHeight );

            // visible on scroll up, hidden on scroll down
            if ( lastScrollY !== window.scrollY )
                $masthead.classList.toggle( 'is-visible', lastScrollY > window.scrollY );
        }

        lastScrollY = window.scrollY
    }, false);

})();