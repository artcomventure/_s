(function() {

  // `hash` is URL-encoded and may contain characters invalid in selectors
  function getTarget( hash ) {
    try {
      return document.getElementById( decodeURIComponent( hash.slice( 1 ) ) );
    }
    catch {
      return null;
    }
  }

  const io = new IntersectionObserver( entries => {
    entries.forEach( entry => {
      if ( !entry.target.isConnected ) return io.unobserve( entry.target );

      if ( !entry.isIntersecting ) return;

      const $anchor = document.querySelector( `[href="#${CSS.escape( entry.target.id )}"]` );
      if ( !$anchor ) return;

      $anchor.parentElement.querySelectorAll( 'a' ).forEach( $link => {
        $link.classList.remove( 'active' );
      } );

      $anchor.classList.add( 'active' );
    } )
  }, { rootMargin: '-50% 0% -50% 0%', threshold: [0,1] } );

  // behaviour for all anchor links (not anchor navigation only)
  Behaviours.add( 'anchor-navigation', $context => {

    const $anchors = $context.querySelectorAll('a[href*="#"]' );
    if ( !$anchors.length ) return;

    $anchors.forEach( $anchor => {
      // anchor must be on the current page
      if ( !$anchor.hash || $anchor.pathname !== window.location.pathname ) return;

      const $element = getTarget( $anchor.hash );
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

        // move focus, so next tab continues at target (e.g. skip link)
        if ( !$element.matches( 'a[href], button, input, select, textarea, [tabindex]' ) )
          $element.setAttribute( 'tabindex', -1 );
        $element.focus( { preventScroll: true } );

        // keep hash for history and sharing
        if ( location.hash !== $anchor.hash ) history.pushState( null, '', $anchor.hash );
      } )

      if ( $anchor.closest( '.anchors' ) ) io.observe( $element );
    } );

  } )

})();