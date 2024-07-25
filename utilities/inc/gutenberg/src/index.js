import { dispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';

// remove template panel
if ( dispatch( 'core/edit-post') )
    dispatch( 'core/edit-post').removeEditorPanel( 'template' );

apiFetch( { path: 'gutenberg/v1/getBackgroundColor' } ).then( ( color ) => {
    const $sheet = document.createElement('style' );

    // set root font size to 10px like frontend
    $sheet.innerHTML = 'html { font-size: 62.5%; }';

    // attach custom background color to editor
    if ( color ) $sheet.innerHTML += "\n" + `.editor-styles-wrapper { background-color: #${color}; }`;

    document.body.appendChild( $sheet );
} );

(function setClientWidthProperty() {
    const $editorStylesWrapper = document.querySelector( '.editor-styles-wrapper' );
    if ( $editorStylesWrapper ) new ResizeObserver( entries => {
        $editorStylesWrapper.style.setProperty( '--rootWidth', `${$editorStylesWrapper.clientWidth}` );
    } ).observe( $editorStylesWrapper );
    else window.requestAnimationFrame( setClientWidthProperty )
})();