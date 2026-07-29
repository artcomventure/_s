// Initially, `wpcf7.init` is called immediately by contact form 7 plugin js.
// But due to Pjax we need to attach js after every "page load".
// To not interfere with the initial call we register this Behaviour after initial attach.
Behaviours.add( 'form:wpcf7:init', $context => {
  Behaviours.remove( 'form:wpcf7:init' );

  if ( typeof wpcf7 === 'undefined' ) return;

  Behaviours.add( 'form:wpcf7:init', $context => {
    $context.querySelectorAll('.wpcf7 > form' ).forEach( wpcf7.init )
  } )
} )

/**
 * Scroll to first error.
 * @since 1.20.8
 */
window.addEventListener( 'wpcf7invalid', e => {
  requestAnimationFrame( () => {
    let $input = e.target.querySelector( '.wpcf7-not-valid' );
    if ( !$input ) return;

    $input.focus()

    // in case of UI js, inputs are often hidden
    // so we have to select the first visible parent element
    while ( ($input.currentStyle || getComputedStyle( $input, null )).display === 'none' ) {
      if ( !$input.parentElement ) return;
      $input = $input.parentElement;
    }

    $input.scrollIntoView( { block: 'center' } )
  } )
} )