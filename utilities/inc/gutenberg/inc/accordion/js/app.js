(function() {

    function accordion( $context ) {

        $context = $context||document;

        let $accordions;

        if ( !!$context.classList && $context.classList.contains( 'wp-block-accordion-widget' ) ) $accordions = [$context];
        else $accordions = $context.getElementsByClassName( 'wp-block-accordion-widget' );
        if ( !$accordions.length ) return;

        [].forEach.call( $accordions, function( $accordion ) {
            // clean up in case options are set via classes
            ['multiple', 'close-all'].forEach( ( option ) => {
                if ( !$accordion.classList.contains( `allow-${option}` ) ) return;

                $accordion.setAttribute( `data-allow-${option}`, '' );
                $accordion.classList.remove( `allow-${option}` );
            } )

            // allow for multiple accordion sections to be expanded at the same time
            const multiple = () => $accordion.hasAttribute( 'data-allow-multiple' );
            const closeAll = () => $accordion.hasAttribute( 'data-allow-close-all' );

            // status
            // state on the header (button)
            const expanded = ( $item ) => $item.firstElementChild.getAttribute( 'aria-expanded' ) === 'true';

            const expand = ( $item ) => {
                $item.firstElementChild.setAttribute( 'aria-expanded', 'true' );
                const $content = $item.querySelector( '.wp-block-accordion-item-content' );
                if ( $content ) $content.removeAttribute( 'inert' );
                $item.dispatchEvent( new CustomEvent( 'accordion-item-expand', { bubbles: true } ) );
                return true;
            }

            const collapse = ( $item ) => {
                $item.firstElementChild.setAttribute( 'aria-expanded', 'false' );
                const $content = $item.querySelector( '.wp-block-accordion-item-content' );
                if ( $content ) $content.setAttribute( 'inert', '' );
                $item.dispatchEvent( new CustomEvent( 'accordion-item-collapse', { bubbles: true } ) );
                return true;
            }

            const toggle = ( $item ) => {
                if ( expanded( $item ) && !closeAll() ) {
                    if ( [].filter.call( $accordion.children, expanded ).length < 2 )
                        return;
                }

                // toggle
                (expanded( $item ) && collapse( $item )) || expand( $item );
            }

            // set item's max height according if expanded or collapsed
            const setMaxHeight = ( $item ) => {
                const $content = $item.querySelector( '.wp-block-accordion-item-content' );
                if ( $content && Math.abs( parseInt($content.style.maxHeight || 0 ) - $content.scrollHeight ) > 1 )
                    $content.style.maxHeight = `${$content.scrollHeight}px`;

                // nested accordions
                while ( ( $item = $item.parentElement.closest( 'div.wp-block-accordion-item' ) ) )
                    setMaxHeight( $item )
            }

            // one observer for all items
            const accordionItemResizeObserver = new ResizeObserver(entries => {
                entries.forEach( function( entry, i ) {
                    // check if element is connected to the `document`
                    if ( !entry.target.isConnected ) return accordionItemResizeObserver.unobserve( entry.target );

                    setMaxHeight( entry.target );
                } );
            } );

            [].forEach.call( $accordion.children, function( $item ) {
                if ( !$item.firstElementChild.hasAttribute( 'aria-expanded' ) ) collapse( $item );

                // a11y
                if ( !$item.firstElementChild.hasAttribute( 'role' ) ) {
                    $item.firstElementChild.setAttribute( 'role', 'button' );
                    $item.firstElementChild.setAttribute( 'tabindex', 0 );
                }

                // connect header (button) with its content
                const $content = $item.querySelector( '.wp-block-accordion-item-content' );
                if ( $content && !$item.firstElementChild.hasAttribute( 'aria-controls' ) ) {
                    if ( !$content.id ) $content.id = $item.id ? `${$item.id}-content` : utlts.uuid( 5, 'accordion-content' );
                    $item.firstElementChild.setAttribute( 'aria-controls', $content.id );
                }

                setMaxHeight( $item ); // TODO: necessary?
                accordionItemResizeObserver.observe( $item );

                $item.firstElementChild.addEventListener( 'click', function( e ) {
                    // close all other items
                    if ( !multiple() ) [].filter.call( $accordion.children, function( $child ) {
                        return $child !== $item;
                    } ).forEach( collapse );

                    toggle( $item );
                }, { passive: true } );

                $item.firstElementChild.addEventListener( 'keydown', e => {
                    // like a native button: Enter and Space, on the header itself only (not nested elements)
                    if ( (e.key !== 'Enter' && e.key !== ' ') || e.target !== e.currentTarget ) return;
                    // Space would scroll the page
                    e.preventDefault();
                    e.target.dispatchEvent( new Event( 'click' ) );
                } )
            } );

            // once per accordion (not per item):
            // … open all items
            if ( $accordion.classList.contains( 'open-all' ) )
                !$accordion.classList.remove( 'open-all' ) && [].forEach.call( $accordion.children, expand );
            // … open first item
            else if ( $accordion.classList.contains( 'open-first' ) )
                !$accordion.classList.remove( 'open-first' ) && expand( $accordion.children[0] );

            $accordion.dispatchEvent( new CustomEvent( 'accordion-init', { bubbles: true } ) );

            // open item by hash (corresponding element id)
            if ( location.hash ) {
                // `hash` is URL-encoded and may contain characters invalid in selectors
                let $item = null;
                try { $item = document.getElementById( decodeURIComponent( location.hash.slice( 1 ) ) ); }
                catch {}

                if ( $item && $item.matches( '.wp-block-accordion-item' ) && $accordion.contains( $item ) ) {
                    // close the others, unless multiple are allowed (like a click)
                    if ( !multiple() ) [].filter.call( $accordion.children, $child => $child !== $item ).forEach( collapse );
                    expand( $item )

                    const duration = getComputedStyle( $item ).transitionDuration;
                    setTimeout( () => $item.scrollIntoView(), parseFloat( duration ) * (/\ds$/.test( duration ) ? 1000 : 1) )
                }
            }

            // enable icon animation after the initial state is rendered (no spin on load)
            requestAnimationFrame( () => requestAnimationFrame( () => $accordion.setAttribute( 'data-ready', '' ) ) );
        } );

    }

    if ( typeof Behaviours !== 'undefined' )
        Behaviours.add( 'accordions', accordion );
    else accordion();

})();
