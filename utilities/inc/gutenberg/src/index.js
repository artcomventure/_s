import { dispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { registerFormatType, toggleFormat } from '@wordpress/rich-text';
import { RichTextToolbarButton } from '@wordpress/block-editor';
import { __, _n } from '@wordpress/i18n';
import { lineDashed } from '@wordpress/icons';

// remove template panel
// dispatch( 'core/editor').removeEditorPanel( 'template' );

// set background color
apiFetch( { path: 'gutenberg/v1/getBackgroundColor' } ).then( ( color ) => {
    const $sheet = document.createElement('style' );

    // set root font size to 10px like frontend
    $sheet.innerHTML = 'html { font-size: 62.5%; }';

    // attach custom background color to editor
    if ( color ) $sheet.innerHTML += "\n" + `.editor-styles-wrapper { background-color: #${color}; }`;

    document.body.appendChild( $sheet );
} );

// set client width property to get fluid to work properly
(function setClientWidthProperty() {
    const $editorStylesWrapper = document.querySelector( '.editor-styles-wrapper' );
    if ( $editorStylesWrapper ) new ResizeObserver( entries => {
        $editorStylesWrapper.style.setProperty( '--rootWidth', `${$editorStylesWrapper.clientWidth}` );
    } ).observe( $editorStylesWrapper );
    else window.requestAnimationFrame( setClientWidthProperty )
})();

// `style="white-space: nowrap"`
registerFormatType( 'utilities-gutenberg/nowrap', {
    title: __( 'no line break', 'gutenberg' ),
    tagName: 'span',
    className: null,
    attributes: {
        style: 'style'
    },
    edit: ( { isActive, onChange, value } ) => {
        return (
            <RichTextToolbarButton
                icon={ lineDashed }
                title={ __( 'no line break', 'gutenberg' ) }
                onClick={ () => {
                    onChange(
                        toggleFormat( value, {
                            type: 'utilities-gutenberg/nowrap',
                            attributes: {
                                style: 'white-space: nowrap'
                            }
                        } )
                    );
                } }
                isActive={ isActive }
            />
        );
    },
} );
