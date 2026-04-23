Behaviours.add( 'video:js', $context => {
  $context.querySelectorAll( 'video' ).forEach( $video => {
    $video.id = $video.id || uuid( 8, 'video' );
    $video.classList.add( 'video-js' )

    const player = videojs( $video, Alter.do( 'videojs:options', {
      fluid: true,
      responsive: true,
      userActions: {
        doubleClick: false
      }
    }, $video ) );
  } )
} )