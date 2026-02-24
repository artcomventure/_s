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