wp.customize.bind( 'ready', function(){

    const $customSelectCssSelectors = document.getElementById( 'customize-control-custom-select-css-selectors' );
    if ( !$customSelectCssSelectors ) return;

    $customSelectCssSelectors.style.width = 'auto';
    $customSelectCssSelectors.style.marginLeft = '24px';

    const $customSelect = document.querySelector( '#customize-control-custom-select [type="checkbox"]' );
    if ( !$customSelect ) return;

    function toggleCssSelectors() {
        $customSelectCssSelectors.style.display = $customSelect.checked ? 'block' : 'none';
    }

    toggleCssSelectors();
    $customSelect.addEventListener( 'change', toggleCssSelectors );

} );