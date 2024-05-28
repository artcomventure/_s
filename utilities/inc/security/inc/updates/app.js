[].forEach.call( document.querySelectorAll( '.wp-list-table.plugins input[type=checkbox][id^=disable-updates-]' ), $input => {
    $input.addEventListener( 'change', e => {
        let url = new URL( $input.value );
        // const data = new FormData();
        //
        // url.searchParams.forEach( (value, key) => {
        //     data.append( key, value );
        //     url.searchParams.delete( key );
        // } );
        //
        // if ( url.searchParams.has( 'plugin' ) ) {
        //     data.append( 'checked', url.searchParams.get( 'plugin' ) );
        //     url.searchParams.delete( 'plugin' );
        // }

        fetch( url.toString(), {
            method: 'POST',
            body: url.searchParams
        } )
            .then( response => '' )
        // console.log( $input.checked, $input.value );
    } )
} );