Behaviours.add( 'load-more', $context => {

  $context.querySelectorAll( '.load-more' ).forEach( $button => {
    $button.addEventListener( 'click', e => {
      e.preventDefault();

      let query;
      try { query = JSON.parse( $button.getAttribute( 'data-query' ) ) }
      catch( e ) { query = {} }

      fetch('/wp-json/teaser-list/v1/query?' + new URLSearchParams( query ))
        .then( response => response.json() )
        .then( data => {
          if ( data.html ) {
            const $tmp = document.createElement( 'div' );
            $tmp.innerHTML = data.html;

            $tmp.querySelectorAll( ':scope > *' ).forEach( $item => {
              $button.before( $item );
              Behaviours.attach( $item );
            } )
          }

          if ( !data.has_more ) $button.remove();
          else $button.setAttribute( 'data-query', JSON.stringify( data.query ) );
        } )
    } )
  } )

} )