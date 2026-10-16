(function() {

    const $panel = document.getElementById( 'dev-info-panel' );
    if ( !$panel ) return;

    const $url = document.createElement( 'div' );
    $url.id = 'url-info';
    $url.className = 'hidden';
    $panel.prepend( $url );
    $panel.setAttribute( 'data-copy', '' );

    function display() {
        const $span = document.createElement( 'span' );
        $span.setAttribute( 'data-copy', wp.i18n.__( 'URL', 'dev' ) );
        $span.textContent = location.href;
        $url.replaceChildren( $span );
    }

    window.addEventListener( 'popstate', display );
    window.addEventListener( 'pjax:success', display );
    display();

})()