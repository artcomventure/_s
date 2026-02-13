/**
 * Sanitize slugs on input.
 */
document.querySelectorAll( 'input[name$=":slug"]' ).forEach( $input => {
    ['change', 'input'].forEach( event => {
        $input.addEventListener( event, e => {
            // remember cursor position
            const start = $input.selectionStart;
            const end = $input.selectionEnd;

            // sanitize slug
            $input.value = wp.url.cleanForSlug( $input.value )

            // set cursor position
            $input.setSelectionRange( start, end );
        } )
    } )
} )