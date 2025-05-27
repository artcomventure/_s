Behaviours.add( 'load-more', $context => {

  let $blocks = $context.querySelectorAll( '.wp-block-posts-list-block' );
  if ( !$blocks.length ) {
    if ( !!$context.classList && $context.classList.contains( 'wp-block-posts-list-block' ) )
      $blocks = [$context];
  }

  $blocks.forEach( $block => {
    const blockId = $block.getAttribute( 'data-uuid' );

    $block.addEventListener( 'click', e => {
      const $button = e.target;
      if ( !$button || $button.tagName !== 'BUTTON' || !$button.classList.contains( 'load-more' ) )
        return;

      $button.classList.add( 'loading' );

      // first I used a custom REST API endpoint which is way faster,
      // but I need to request the posts from url to get proper results

      let url = window.location.pathname;
      url = url.replace( /\/page\/(\d+)\//, '/' );

      const search = new URLSearchParams( window.location.search );
      search.set( 'paged', $button.getAttribute( 'data-next-page' ) )

      fetch( `${url}?${search.toString()}` )
        .then( response => response.text() )
        .then( html => {
          const $html = document.implementation.createHTMLDocument();
          $html.documentElement.innerHTML = html;

          const $newBlock = $html.querySelector( `[data-uuid="${blockId}"]` );
          if ( !$newBlock ) return;

          // will be added again in next step if present
          $button.remove();

          // grep new articles and append to list
          [].forEach.call( $newBlock.children, $child => {
              if ( $child.classList.contains( 'posts-list--do-not-append' ) ) return;

              $block.appendChild( $child = $child.cloneNode( true ) );
              Behaviours.attach( $child );
          } )
        } )
    } )
  } )

} )