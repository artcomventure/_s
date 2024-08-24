Behaviours.add( 'media:image:aspect-ratio', $context => {

  $context.querySelectorAll( 'figure.wp-block-image[data-object-position]' ).forEach( $figure => {
    const $img = $figure.querySelector( 'img' );
    if ( !$img ) return;

    $img.style.objectPosition = $figure.getAttribute( 'data-object-position' );
    $figure.removeAttribute( 'data-object-position' )
  } )

} )