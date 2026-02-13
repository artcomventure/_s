// mediaelementjs is initialized by WP.
// But due to Pjax we need to attach the _script_ after every "page load".
Behaviours.add( 'video:mediaelement', () => {
    Behaviours.remove( 'video:mediaelement' );

    // make sure mediaelement is loaded
    if ( !wp?.mediaelement ) return;
    Behaviours.add( 'video:mediaelement', () => wp.mediaelement.initialize() );
} );

// WP' medielement script only take ".wp-video-shortcode" into account.
// We duplicate `wp.mediaelement.initialize()` to cover `.wp-block-video`.
Behaviours.add( 'video:wp-block-video', () => {
    var selectors = [];

    if ( typeof _wpmejsSettings !== 'undefined' ) {
        settings = jQuery.extend( true, {}, _wpmejsSettings );
    }
    settings.classPrefix = 'mejs-';
    settings.success = settings.success || function ( mejs ) {
        var autoplay, loop;

        if ( mejs.rendererName && -1 !== mejs.rendererName.indexOf( 'flash' ) ) {
            autoplay = mejs.attributes.autoplay && 'false' !== mejs.attributes.autoplay;
            loop = mejs.attributes.loop && 'false' !== mejs.attributes.loop;

            if ( autoplay ) {
                mejs.addEventListener( 'canplay', function() {
                    mejs.play();
                }, false );
            }

            if ( loop ) {
                mejs.addEventListener( 'ended', function() {
                    mejs.play();
                }, false );
            }
        }
    };

    /**
     * Custom error handler.
     *
     * Sets up a custom error handler in case a video render fails, and provides a download
     * link as the fallback.
     *
     * @since 4.9.3
     *
     * @param {object} media The wrapper that mimics all the native events/properties/methods for all renderers.
     * @param {object} node  The original HTML video, audio, or iframe tag where the media was loaded.
     * @return {string}
     */
    settings.customError = function ( media, node ) {
        // Make sure we only fall back to a download link for flash files.
        if ( -1 !== media.rendererName.indexOf( 'flash' ) || -1 !== media.rendererName.indexOf( 'flv' ) ) {
            return '<a href="' + node.src + '">' + mejsL10n.strings['mejs.download-file'] + '</a>';
        }
    };

    if ( 'undefined' === typeof settings.videoShortcodeLibrary || 'mediaelement' === settings.videoShortcodeLibrary ) {
        selectors.push( '.wp-block-video video' );
    }
    if ( ! selectors.length ) {
        return;
    }

    // Only initialize new media elements.
    jQuery( selectors.join( ', ' ) )
    .not( '.mejs-container' )
    .filter(function () {
        return ! jQuery( this ).parent().hasClass( 'mejs-mediaelement' );
    })
    .each(function (){
        jQuery( this ).attr( 'preload', jQuery( this ).attr( 'preload' ) || 'metadata' );
    })
    .mediaelementplayer( settings );
} );