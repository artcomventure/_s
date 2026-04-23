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
                slidesPerView = ++i;
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
    if ( swiper.params.spaceBetween ) new ResizeObserver( entries => {
        // progress from mobile to desktop
        const progress = Math.max( 0, Math.min( 1, (window.innerWidth - BREAKPOINTS.mobile) / (BREAKPOINTS.desktop - BREAKPOINTS.mobile) ) );
        // calculate space between
        swiper.params.spaceBetween = Math.max(8, swiper.originalParams.spaceBetween * progress )

        swiper.update()
    } ).observe( document.documentElement );
}, { passive: true } );

/**
 * Toggle header.
 */
(function() {

    const threshold = 11 // min distance to be scrolled before making actions
    let $masthead;

    Behaviours.add( 'header:toggle', $context => {

        let $primary = $context.querySelector( '#primary' )
        if ( !$primary && $context?.id === 'primary' )
            $primary = $context;

        if ( $primary ) {
            if ( ($masthead && $masthead.parentElement ) || ($masthead = document.getElementById( 'masthead' )) )
                $masthead.addEventListener( 'mouseover', e => $masthead.classList.add( 'is-visible' ) )
        }
        // else $masthead = undefined

    } )

    let lastScrollY = 0;
    window.addEventListener('scroll', e => {
        if ( Math.abs( window.scrollY - lastScrollY ) < threshold ) return

        if ( $masthead ) {
            $masthead.classList[window.scrollY < $masthead.offsetHeight ? 'remove' : 'add']( 'is-togglable' );

            const action = lastScrollY < window.scrollY
                ? /* scroll up */ 'remove'
                : lastScrollY > window.scrollY ? /* scroll down */ 'add' : ''

            if ( action ) $masthead.classList[action]( 'is-visible' );
        }

        lastScrollY = window.scrollY
    }, false);

})();