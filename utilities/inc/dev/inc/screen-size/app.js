(function() {

    const $panel = document.getElementById( 'dev-info-panel' );
    if ( !$panel ) return;

    const $info = document.createElement( 'div' );
    $info.id = 'screen-size-info';
    $panel.prepend( $info );

    function display( breakpoint ) {
        if ( typeof breakpoint !== 'undefined' ) {
            return `<span${BREAKPOINTS[breakpoint] ? ' title="' + BREAKPOINTS[breakpoint] + 'px"' : '' }>${breakpoint}</span>`
        }

        let data = [
            `<span data-copy="${wp.i18n.__( 'Screen size', 'dev' )}" title="${wp.i18n.__( 'Screen size', 'dev' )}">${window.innerWidth}&times;${window.innerHeight}</span>`
        ]

        if ( typeof BREAKPOINTS !== 'undefined' ) {
            const breakpoints = Object.keys( BREAKPOINTS )
                .filter( breakpoint => typeof BREAKPOINTS[breakpoint] !== 'function' && ['mobile', 'desktop'].indexOf( breakpoint ) < 0 );

            if ( breakpoints.length ) {
                let range;
                breakpoints.forEach( ( breakpoint, i) => {
                    if ( !range && BREAKPOINTS.down( breakpoint ) ) {
                        range = (breakpoints[i-1] ? display( breakpoints[i-1] ) + ' - ' : '0 - ')
                          + display( breakpoint );
                    }
                } )
                data.push( `<span title="${wp.i18n.__( 'Breakpoint range', 'dev' )}">${range || display( breakpoints[breakpoints.length - 1] ) + ' - ' + window.innerWidth}</span>` );
            }

            let layout = wp.i18n.__( 'Tablet', 'dev' );
            if ( BREAKPOINTS.isMobile() ) layout = wp.i18n.__( 'Mobile', 'dev' );
            else if ( BREAKPOINTS.isDesktop() ) layout = wp.i18n.__( 'Desktop', 'dev' );
            data.push( `<span title="${wp.i18n.__( 'Layout', 'dev' )}">${layout}</span>` );
        }

        $info.innerHTML = data.join( '' );
    }

    window.addEventListener( 'resize', e => display() );
    display();

})()