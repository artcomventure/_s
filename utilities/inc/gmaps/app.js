(function() {

  async function initMap( $map ) {
    const { Map } = await google.maps.importLibrary( "maps" );

    const options = {
      zoom: gmaps.settings.zoom,
      center: gmaps.settings.center,
      // disable the default User Interface
      disableDefaultUI: true,
      // add back fullscreen, streetview, zoom
      zoomControl: true,
      streetViewControl: false,
      fullscreenControl: true
    }

    // map id (cloud styling, needed for advanced markers), local styles or demo map id (development)
    if ( gmaps.settings.mapId ) options.mapId = gmaps.settings.mapId;
    else if ( gmaps.settings.styles.length ) options.styles = gmaps.settings.styles;
    else options.mapId = 'DEMO_MAP_ID';

    // the map
    let map = new Map( $map, Alter.do( 'gmap:options', options, $map ) );

    // make sure the map is initialized
    const idleListener = map.addListener( 'idle', () => {
      google.maps.event.removeListener( idleListener );

      $map.dispatchEvent( new CustomEvent( 'gmap:init', {
        bubbles: true,
        detail: map
      } ) )
    } );
  }

  Behaviours.add( 'gmap:init', $context => {
    $context.querySelectorAll( '.wp-block-gmap-block > .map:not(:has(.gm-style))' ).forEach( initMap )
  } )

})()