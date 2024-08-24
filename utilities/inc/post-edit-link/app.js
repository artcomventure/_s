// _in_accessibility
Behaviours.add( 'post-edit-link:accessibility', $context => {

    $context.querySelectorAll( 'a.post-edit-link' ).forEach( $postEditLink => {
        $postEditLink.setAttribute( 'tabindex', -1 );
        $postEditLink.setAttribute( 'aria-hidden', 'true' );
    } )

} )

// filter post edit link
// see `THEME/utilities/inc/accessibility`
Alter.add( 'accessibility:shy', $element => {
    const $postEditLink = $element.querySelector( 'a.post-edit-link' );
    if ( $postEditLink ) $postEditLink.remove();

    return $element;
} )