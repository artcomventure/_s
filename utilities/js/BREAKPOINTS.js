// ---
// Retrieve layout breakpoints defined in css.

(function() {

    const $html = document.documentElement;

    // `--rootWidth` is initially set in `THEME/header.php`
    new ResizeObserver( entries => {
        $html.style.setProperty( '--rootWidth', `${$html.clientWidth}` );
    } ).observe( $html );

    window.BREAKPOINTS = {
        up: size => $html.clientWidth >= (BREAKPOINTS[size] || size),
        down: size => $html.clientWidth < (BREAKPOINTS[size] || size),

        isMobile: () => BREAKPOINTS.down( 'mobile' ),
        isTablet: () => BREAKPOINTS.up( 'mobile' ) && BREAKPOINTS.down( 'desktop' ),
        isDesktop: () => BREAKPOINTS.up( 'desktop' )
    };

    const CSS = getComputedStyle( document.documentElement );

    ['xxs', 'xs', 'sm', 'md', 'lg', 'xl', 'xxl', 'mobile', 'desktop', 'content', 'wide'].forEach( size => {
        const value = CSS.getPropertyValue(`--width-${size}`);
        if ( !value ) return;

        BREAKPOINTS[size] = parseInt( value );
    } )

    // is mobile device
    const isMobileDevice = document.body.classList.contains( 'mobile' );
    window.isMobileDevice = () => isMobileDevice;

})();