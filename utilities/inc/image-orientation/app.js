(function() {

    function mediaOrientation( $image, orientation ) {
        // $image `attribute` is an event
        if ( $image.tagName !== 'IMG' ) {
            $image = $image.target;
        }

        const $figure = $image.closest( 'figure' );

        // given `orientation` means image already got ratio class
        // ... but we'll check for figure
        if ( orientation ) {
            if ( $figure ) $figure.classList.add( `ratio-${orientation}` );
            return;
        }

        orientation = 'square';
        if ( $image.naturalWidth > $image.naturalHeight ) orientation = 'landscape';
        else if ( $image.naturalWidth < $image.naturalHeight ) orientation = 'portrait';

        $image.classList.add( `ratio-${orientation}` );
        mediaOrientation( $image, orientation ); // figure
    }

    Behaviours.add( 'media:orientation', ( $context ) => {

        [].forEach.call( $context.querySelectorAll( 'img' ), $image => {
            // check for already given orientation
            let orientation = ($image.className.match( /(^| )ratio-([^ ])( |$)/ )||[])[2];

            if ( orientation  ) mediaOrientation( $image, orientation );
            else if ( $image.complete ) mediaOrientation( $image )
            else $image.addEventListener( 'load', mediaOrientation );
        } )

    } );

})();