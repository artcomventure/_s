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