import { dispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { addFilter } from '@wordpress/hooks';
import classnames from "classnames";

// remove template panel
dispatch( 'core/editor').removeEditorPanel( 'template' );

apiFetch( { path: 'gutenberg/v1/getBackgroundColor' } ).then( ( color ) => {
    const $sheet = document.createElement('style' );

    // set root font size to 10px like frontend
    $sheet.innerHTML = 'html { font-size: 62.5%; }';

    // attach custom background color to editor
    if ( color ) $sheet.innerHTML += "\n" + `.editor-styles-wrapper { background-color: #${color}; }`;

    document.body.appendChild( $sheet );
} );

/**
 * Set client width property to get fluid to work properly.
 */
(function setClientWidthProperty() {
    const $editorStylesWrapper = document.querySelector( '.editor-styles-wrapper' );
    if ( $editorStylesWrapper ) new ResizeObserver( entries => {
        $editorStylesWrapper.style.setProperty( '--rootWidth', `${$editorStylesWrapper.clientWidth}` );
    } ).observe( $editorStylesWrapper );
    else window.requestAnimationFrame( setClientWidthProperty )
})();