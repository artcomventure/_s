(function() {

  const io = new IntersectionObserver( entries => {
    entries.forEach( entry => {
      if ( !entry.target.parentElement ) return io.unobserve( entry.target );
      if ( !entry.isIntersecting ) return;

      entry.target.innerHTML = entry.target.getAttribute( 'data-value' )

      io.unobserve( entry.target )
    } )
  }, {
    threshold: .5,
    rootMargin: '70% 0% -30%'
  } )

  Behaviours.add( 'odometer', $context => {
    if ( typeof Odometer !== 'function' ) return;

    $context.querySelectorAll( '.odometer' ).forEach( $odometer => {

      const value = $odometer.innerHTML;
      $odometer.setAttribute( 'data-value', value );

      new Odometer( {
        el: $odometer,
        //value: 1 + '0'.repeat( (value + '').length - 1 ),
        value: 0
      } )

      io.observe( $odometer )
    } )
  } );

})();