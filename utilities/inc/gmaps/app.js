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

    // map id or styles
    if ( gmaps.settings.mapId || !gmaps.settings.styles )
      options.mapId = gmaps.settings.mapId || 'DEMO_MAP_ID';
    if ( gmaps.settings.styles.length && !gmaps.settings.mapId )
      options.styles = gmaps.settings.styles;

    // the map
    let map = new Map( $map, Alter.do( 'gmap:options', options ), $map );

    // make sure the map is initialized
    const idleListener = map.addListener( 'idle', () => {
      google.maps.event.removeListener( idleListener );

      $map.dispatchEvent( new CustomEvent( 'map:init', {
        bubbles: true,
        detail: map
      } ) )
    } );
  }

  Behaviours.add( 'map:init', $context => {
    $context.querySelectorAll( '.wp-block-gmap-block > .map:not(:has(.gm-style))' ).forEach( initMap )
  } )

})()