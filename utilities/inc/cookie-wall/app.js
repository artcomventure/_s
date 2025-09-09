// Initially, the open dialog functionality is attached by borlabs itself.
// But due to Pjax we need to attach it after every "page load".
Behaviours.add( 'cookie-wall:borlabs', $context => {
  Behaviours.remove( 'cookie-wall:borlabs' )

  // make sure Borlabs is in use
  if ( typeof BorlabsCookie === 'undefined' ) return;

  Behaviours.add( 'cookie-wall:borlabs', $context => {
    $context.querySelectorAll( '.borlabs-cookie-open-dialog-preferences' ).forEach( $element => {
      const $widget = document.querySelector('.brlbs-cmpnt-widget' )
      if ( !$widget ) return;

      $element.addEventListener( 'click', e => {
        e.preventDefault();

        $widget.dispatchEvent( new Event( 'click' ) )
      } )
    } )
  } )

} )

// remove from pjax
Alter.add( 'pjax-elements', $elements => {
  return $elements.filter( $element => {
    return !$element.classList.contains( 'borlabs-cookie-open-dialog-preferences' )
  } )
} )