// Hide controls via `Alter.add( 'videojs:options', … )` (e.g. `controlBar: { progressControl: false }`),
// not `player.controlBar.removeChild()`: removed components aren't disposed with the player (their intervals keep running).
Behaviours.add( 'video:js', $context => {
  $context.querySelectorAll( 'video' ).forEach( $video => {
    // already initialized
    if ( $video.classList.contains( 'vjs-tech' ) ) return;

    $video.id = $video.id || utlts.uuid( 8, 'video' );
    $video.classList.add( 'video-js' )

    videojs( $video, Alter.do( 'videojs:options', {
      fluid: true,
      responsive: true,
      userActions: {
        doubleClick: false
      }
    }, $video ) );
  } )
} )