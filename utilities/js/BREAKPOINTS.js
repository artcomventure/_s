// ---
// Retrieve layout breakpoints defined in css.

(function() {

    const $html = document.documentElement;

    window.BREAKPOINTS = {
        up: size => BREAKPOINTS[size] && ($html.clientWidth >= BREAKPOINTS[size]),
        down: size => BREAKPOINTS[size] && ($html.clientWidth < BREAKPOINTS[size]),

        isMobile: () => BREAKPOINTS.down( 'mobile' ),
        isTablet: () => BREAKPOINTS.up( 'mobile' ) && BREAKPOINTS.down( 'desktop' ),
        isDesktop: () => BREAKPOINTS.up( 'desktop' )
    };

    const CSS = getComputedStyle( document.documentElement );

    ['xxs', 'xs', 'sm', 'md', 'lg', 'xl', 'xxl', 'mobile', 'desktop'].forEach( size => {
        const value = CSS.getPropertyValue(`--width-${size}`);
        if ( !value ) return;

        BREAKPOINTS[size] = parseInt( value );
    } )

    // is mobile device
    const isMobileDevice = document.body.classList.contains( 'mobile' );
    window.isMobileDevice = () => isMobileDevice;

})();