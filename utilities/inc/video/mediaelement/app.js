// mediaelementjs: WP initializes `.wp-video-shortcode`/`.wp-audio-shortcode` on DOM ready only and ignores
// the video block, so initialize all of them per `$context` (also after AJAX navigation).
// Based on `wp.mediaelement.initialize()` (`wp-includes/js/mediaelement/wp-mediaelement.js`).
Behaviours.add( 'video:mediaelement', $context => {
    if ( typeof jQuery === 'undefined' || !jQuery.fn.mediaelementplayer ) return;

    const settings = typeof _wpmejsSettings !== 'undefined' ? jQuery.extend( true, {}, _wpmejsSettings ) : {};
    settings.classPrefix = 'mejs-';
    settings.success = settings.success || function( mejs ) {
        if ( !mejs.rendererName || mejs.rendererName.indexOf( 'flash' ) === -1 ) return;

        if ( mejs.attributes.autoplay && mejs.attributes.autoplay !== 'false' ) mejs.addEventListener( 'canplay', () => mejs.play(), false );
        if ( mejs.attributes.loop && mejs.attributes.loop !== 'false' ) mejs.addEventListener( 'ended', () => mejs.play(), false );
    };

    // download link as fallback for flash files
    settings.customError = function( media, node ) {
        if ( media.rendererName.indexOf( 'flash' ) !== -1 || media.rendererName.indexOf( 'flv' ) !== -1 ) {
            return '<a href="' + node.src + '">' + mejsL10n.strings['mejs.download-file'] + '</a>';
        }
    };

    const selectors = [];
    if ( settings.audioShortcodeLibrary === undefined || settings.audioShortcodeLibrary === 'mediaelement' ) selectors.push( '.wp-audio-shortcode' );
    if ( settings.videoShortcodeLibrary === undefined || settings.videoShortcodeLibrary === 'mediaelement' ) selectors.push( '.wp-video-shortcode', '.wp-block-video video' );
    if ( !selectors.length ) return;

    jQuery( $context ).find( selectors.join( ', ' ) )
        // not initialized yet (mejs 4 moves the media into `.mejs-container`)
        .filter( function() {
            return !this.closest( '.mejs-container' );
        } )
        .each( function() {
            this.setAttribute( 'preload', this.getAttribute( 'preload' ) || 'metadata' );
        } )
        .mediaelementplayer( settings );
} );
