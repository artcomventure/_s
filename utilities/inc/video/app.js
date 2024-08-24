Behaviours.add( 'media:video', $context => {
    $context.querySelectorAll( '.video-html' ).forEach( $video => {
        $video.addEventListener( 'click', e => {
            const $iframe = new DOMParser().parseFromString( $video.innerText, 'text/html' )
                .documentElement.querySelector( 'iframe' )

            $video.replaceWith( $iframe );

        }, false );

        $video.addEventListener( 'keydown', e => {
            if ( e.key !== 'Enter' ) return;
            $video.dispatchEvent( new Event( 'click' ) );
        }, false )
    } )
} )