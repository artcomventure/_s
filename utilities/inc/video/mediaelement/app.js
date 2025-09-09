(function() {

    const attachMediaelement = ( $context ) => {
        $context = $context || document;
        if ( typeof $context.querySelectorAll !== 'function' ) return;

        let $videos = $context.querySelectorAll( '.wp-block-video:not(.native-player) > video' );
        if ( !$videos.length && $context.nodeName === 'video' )
            $videos = [$context];

        [].forEach.call( $videos, $video => {

            const options = {
                stretching: 'responsive',
                autoRewind: false,
                // t9n
                playText: wp.i18n.__( 'Play', 'mediaelement' ),
                pauseText: wp.i18n.__( 'Pause', 'mediaelement' ),
                fullscreenText: wp.i18n.__( 'Fullscreen', 'mediaelement' ),
                muteText: wp.i18n.__( 'Mute', 'mediaelement' ),
                unmuteText: wp.i18n.__( 'Unmute', 'mediaelement' )
            }

            if ( !$video.controls ) options['features'] = [];

            new MediaElementPlayer( $video, Alter.do( 'mediaelement:options', options, $video ) );
        } )
    }

    // on page load
    attachMediaelement();

    // on ajax load
    new MutationObserver(function ( entries ) {
        entries.forEach( ( entry) => {
            entry.addedNodes.forEach( attachMediaelement );
        } );
    } ).observe( document.documentElement, {
        subtree: true,
        childList: true
    } );

})();
