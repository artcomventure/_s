// Initially `doSliders` is called immediately by slider plugin's js.
// But due to Pjax we need to attach swiper js after every "page load".
// To not interfere with the initial call we register _slider Behaviour_ after initial attach.
Behaviours.add( 'media:sliders', () => {
    Behaviours.remove( 'media:sliders' );

    // make sure swiper js is loaded
    if ( typeof doSliders !== 'function' ) return;
    Behaviours.add( 'media:sliders', doSliders );
} );

// This event is triggered once a slider is initialized.
// Do any slider adjustment here!
window.addEventListener( 'swiper:afterInit', e => {
    const swiper = e.detail;
}, { passive: true } );

// Close CCM widget on privacy policy page.
Behaviours.add( 'gdpr:privacy-policy', $context => {

    if ( !document.body.classList.contains( 'privacy-policy' ) ) return;
    if ( typeof CCM === 'undefined' ) return;

    CCM.closeWidget()

} )