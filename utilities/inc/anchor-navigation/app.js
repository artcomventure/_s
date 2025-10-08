(function() {

  const io = new IntersectionObserver( entries => {
    entries.forEach( entry => {
      if ( !entry.target.parentElement ) return io.unobserve( entry.target );

      if ( !entry.isIntersecting ) return;

      const $anchor = document.querySelector( `[href="#${entry.target.id}"]` );
      if ( !$anchor ) return;

      $anchor.parentElement.querySelectorAll( 'a' ).forEach( $link => {
        $link.classList.remove( 'active' );
      } );

      $anchor.classList.add( 'active' );
    } )
  }, { rootMargin: '-50% 0% -50% 0%', threshold: [0,1] } );

  Behaviours.add( 'anchor-navigation', $context => {

    const $anchors = $context.querySelectorAll('article.full-view a[href^="#"]' );
    if ( !$anchors.length ) return;

    $anchors.forEach( $anchor => {
      const $element = document.querySelector( $anchor.getAttribute( 'href' ) );
      if ( !$element ) return;

      $anchor.addEventListener( 'click', e => {
        e.preventDefault();

        // const $nav = $anchor.closest( '.anchors' );
        // if ( $nav ) setTimeout( () => {
        //   $nav.querySelectorAll( 'a' ).forEach( $link => {
        //     $link.classList.remove( 'active' );
        //   } );
        //
        //   $anchor.classList.add( 'active' );
        // }, 400 )

        $element.scrollIntoView();
        // re-do scroll in case something changes on scroll action
        setTimeout( () => $element.scrollIntoView(), 100 )
      } )

      if ( $anchor.closest( '.anchors' ) ) io.observe( $element );
    } );

  } )

})();