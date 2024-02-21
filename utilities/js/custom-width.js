// ---
// Custom widths.
// Sometimes widths aren't _normalized_ so this ís a way to apply them dynamically within the backend.

Behaviours.add( 'custom-width', ( $context ) => {

    // convert custom container width shorthand
    [].forEach.call( $context.querySelectorAll( '[class*="container-"]' ), ( $element ) => {
        const matches = $element.className.match( /(^| )container-(\d+)($| )/ );
        if ( !matches ) return;

        $element.classList.remove( matches[0].trim() );
        $element.classList.add( 'container', `width-${matches[2]}` );
    } );

    // set custom width
    [].forEach.call( $context.querySelectorAll( '[class*="width-"]' ), ( $element ) => {
        let width = ($element.className.match( /(^| )width-(\d+)($| )/ )||[])[2];
        if ( !width ) return;

        $element.style.width = `${width}px`;
    } );

} );