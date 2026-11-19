/**
 * Videos (native, video.js, MediaElement.js) play only while rendered (no `display: none` on video or ancestors) and in viewport.
 * Leaving pauses, entering resumes the state the video had when it left.
 * Opt-out: class `ignore-visibility` / attribute `data-ignore-visibility` on video or ancestor,
 * or filter `Alter.add( 'video-visibility:ignore', ( isIgnored, $video ) => isIgnored )`.
 */
(function() {
    const states = new WeakMap(); // $video => { isBound, isVisible, shouldPlay, isAutoPaused }

    function getState( $video ) {
        if ( !states.has( $video ) ) states.set( $video, { isBound: false, isVisible: false, shouldPlay: false, isAutoPaused: false } );
        return states.get( $video );
    }

    function isIgnored( $video ) {
        const isIgnored = !!$video.closest( '.ignore-visibility, [data-ignore-visibility]' );
        return typeof Alter !== 'undefined' ? Alter.do( 'video-visibility:ignore', isIgnored, $video ) : isIgnored;
    }

    function update( $video ) {
        const state = states.get( $video );

        // removed (e.g. disposed on page switch): `load()` fires `loadstart`, don't play it again
        if ( !$video.isConnected ) return;

        // picture-in-picture is visible regardless of the page
        if ( document.pictureInPictureElement === $video ) return;

        if ( state.isVisible ) {
            if ( state.shouldPlay && $video.paused ) $video.play()?.catch( () => {} ); // AbortError: retried on `loadstart`
        }
        else if ( !$video.paused ) {
            state.isAutoPaused = true;
            $video.pause();
        }
    }

    // not rendered elements never intersect, so one observer covers viewport + `display: none`
    const io = new IntersectionObserver( entries => entries.forEach( entry => {
        const $video = entry.target;
        const state = states.get( $video );
        if ( !state?.isBound || !$video.isConnected ) return io.unobserve( $video );

        state.isVisible = entry.isIntersecting;
        update( $video );
    } ) );

    // take over autoplay: browser (and video.js) would also start hidden and off-screen videos
    function takeOver( $video ) {
        getState( $video ).shouldPlay = true;
        $video.autoplay = false;
        $video.dataset.autoplay = '';

        // browser may have started already (cached); async `pause` event mustn't count as user action
        if ( !$video.paused ) {
            getState( $video ).isAutoPaused = true;
            $video.pause();
        }
    }

    // video.js: disable its own autoplay
    if ( typeof Alter !== 'undefined' ) Alter.add( 'videojs:options', ( options, $video ) => {
        if ( !options.autoplay && !$video.autoplay ) return options;
        if ( isIgnored( $video ) ) return options;

        takeOver( $video );
        return Object.assign( {}, options, { autoplay: false } );
    }, 99 );

    // after `video:js` (priority 10): video.js may replace the `<video>` element (iOS)
    Behaviours.add( 'video:visibility', $context => {
        $context.querySelectorAll( 'video' ).forEach( $video => {
            if ( isIgnored( $video ) ) return;

            const state = getState( $video );
            if ( state.isBound ) return;
            state.isBound = true;

            if ( $video.autoplay || 'autoplay' in $video.dataset ) takeOver( $video );

            // remember user's play/pause (our own pauses don't count)
            $video.addEventListener( 'pause', () => {
                if ( state.isAutoPaused ) state.isAutoPaused = false;
                else if ( $video.readyState ) state.shouldPlay = false; // source reset isn't a user action
            } );
            $video.addEventListener( 'play', () => state.shouldPlay = true );

            // (re)set source (e.g. by video.js after init) aborts playback
            $video.addEventListener( 'loadstart', () => update( $video ) );

            // back from picture-in-picture: page may have scrolled away meanwhile
            $video.addEventListener( 'leavepictureinpicture', () => update( $video ) );

            io.observe( $video );
        } );
    }, 11 );
})();
