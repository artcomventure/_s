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
        const expanded = ( $item ) => $item.getAttribute( 'aria-expanded' ) === 'true';
        const collapsed = ( $item ) => !expanded( $item );

        const expand = ( $item ) => {
            $item.setAttribute( 'aria-expanded', 'true' );
            $item.dispatchEvent( new CustomEvent( 'accordion-item-expand', { bubbles: true } ) );
            return true;
        }

        const collapse = ( $item ) => {
            $item.setAttribute( 'aria-expanded', 'false' );
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
            if ( expanded( $item ) ) $item.style.maxHeight = `${$item.scrollHeight}px`;
            else $item.style.maxHeight = `${$item.firstElementChild.scrollHeight}px`;

            // nested accordions
            while ( $item = $item.parentElement.closest( 'div.wp-block-accordion-item' ) )
                setMaxHeight( $item )
        }

        [].forEach.call( $accordion.children, function( $item ) {
            if ( !$item.hasAttribute( 'aria-expanded' ) ) collapse( $item );

            $item.firstElementChild.addEventListener( 'click', function( e ) {
                // close all other items
                if ( !multiple() ) [].filter.call( $accordion.children, function( $child ) {
                    return $child !== $item;
                } ).forEach( collapse );

                toggle( $item );

                [].forEach.call( $accordion.children, setMaxHeight );
            }, { passive: true } );

            const accordionItemResizeObserver = new ResizeObserver(items => {
                items.forEach( function( item, i ) {
                    setMaxHeight( item.target );
                } );
            } );

            [].forEach.call( $accordion.children, ( $item ) => accordionItemResizeObserver.observe( $item ) );

            // … open all items
            if ( $accordion.classList.contains( 'open-all' ) )
                !$accordion.classList.remove( 'open-all' ) && [].forEach.call( $accordion.children, expand );
            // … open first item
            else if ( $accordion.classList.contains( 'open-first' ) )
                !$accordion.classList.remove( 'open-first' ) && expand( $accordion.children[0] );

            $accordion.dispatchEvent( new CustomEvent( 'accordion-init', { bubbles: true } ) );
        } );
    } );

}

accordion(); // init