// tabs UI
(function() {

  // get tabs
  const $tabs = document.querySelectorAll( 'nav.nav-tab-wrapper a' );
  if ( !$tabs.length ) return;

  // array of corresponding panels
  const $panels = [];

  [].forEach.call( $tabs, $tab => {
    // get corresponding panel
    const $panel = document.getElementById( $tab.getAttribute( 'aria-controls' ) );
    if ( !$panel ) return;

    $panels.push( $panel );

    $tab.addEventListener( 'click', e => {
      e.preventDefault();

      // window.history.pushState({},"", e.target.href);

      // tab is already selected
      if ( $tab.getAttribute( 'aria-selected' ) === 'true' ) return;

      // reset all tabs
      [].forEach.call( $tabs, $tab => {
        $tab.setAttribute( 'aria-selected', !!$tab.classList.remove( 'nav-tab-active' ) );
      } );

      // select current tab
      $tab.setAttribute( 'aria-selected', !$tab.classList.add( 'nav-tab-active' ) );

      // reset all panels
      $panels.forEach( $panel => {
        $panel.style.display = 'none';
      } );

      // show selected panel
      $panel.style.display = '';

      window.dispatchEvent( new Event( 'resize' ) );
    } )
  }, false );

})();

document.addEventListener( 'map:init', async e => {
  const map = e.detail;

  const $zoom = document.querySelector( '[name="gmaps[zoom]"]' );
  if ( $zoom ) {
    map.addListener( 'zoom_changed', e => {
      $zoom.value = map.getZoom();
    } )

    $zoom.addEventListener( 'input', e => {
      // manual input only
      if ( !e.isTrusted ) return;

      const zoom = parseInt( $zoom.value );
      if ( isNaN( zoom ) ) return;

      map.setZoom( zoom )
    } )
  }

  const $lat = document.querySelector( '[name="gmaps[center][lat]"]' );
  if ( $lat ) {
    const $lng = document.querySelector( '[name="gmaps[center][lng]"]' );
    if ( $lng ) {
      map.addListener( 'center_changed', e => {
        $lat.value = map.getCenter().lat()
        $lng.value = map.getCenter().lng()
      } )

      const { LatLng } = await google.maps.importLibrary("core" );

      $lat.addEventListener( 'input', setCenterListener )
      $lng.addEventListener( 'input', setCenterListener )
      function setCenterListener( e ) {
        // manual input only
        if ( !e.isTrusted ) return;

        const lat = parseFloat( $lat.value );
        if ( isNaN( lat ) ) return;

        const lng = parseFloat( $lng.value );
        if ( isNaN( lng ) ) return;

        map.setCenter( new LatLng( lat, lng ) );
      }
    }
  }
} )

