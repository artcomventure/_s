import apiFetch from '@wordpress/api-fetch';
import { registerFormatType, toggleFormat } from '@wordpress/rich-text';
import { RichTextToolbarButton } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { lineDashed } from '@wordpress/icons';

// remove template panel
// dispatch( 'core/editor').removeEditorPanel( 'template' );

/**
 * Find .editor-styles-wrapper in the parent document or any editor iframe (WP7+).
 */
function getEditorStylesWrapper() {
    const $el = document.querySelector( '.editor-styles-wrapper' );
    if ( $el ) return $el;

    for ( const $iframe of document.querySelectorAll( 'iframe' ) ) {
        const $wrapper = $iframe.contentDocument?.querySelector( '.editor-styles-wrapper' );
        if ( $wrapper ) return $wrapper;
    }

    return null;
}

// set background color
apiFetch( { path: 'gutenberg/v1/getBackgroundColor' } ).then( ( color ) => {
    (function injectStyles() {
        const $wrapper = getEditorStylesWrapper();
        if ( !$wrapper ) return requestAnimationFrame( injectStyles );

        const doc = $wrapper.ownerDocument;
        const $sheet = doc.createElement( 'style' );

        // set root font size to 10px like frontend
        $sheet.innerHTML = 'html { font-size: 62.5%; }';

        // attach custom background color to editor
        if ( color ) $sheet.innerHTML += "\n" + `.editor-styles-wrapper { background-color: #${color}; }`;

        doc.head.appendChild( $sheet );
    })();
} );

// set scrollbar width property
(function setScrollbarWidthProperty() {
    const $editorStylesWrapper = getEditorStylesWrapper();
    if ( !$editorStylesWrapper ) return requestAnimationFrame( setScrollbarWidthProperty );

    // actual scrollbar of the (iframe's) document, like the frontend (`utilities.php`)
    const doc = $editorStylesWrapper.ownerDocument;
    const win = doc.defaultView;
    const $root = doc.documentElement;

    const setProperty = () => $editorStylesWrapper.style.setProperty( '--scrollbar-width', `${win.innerWidth - $root.clientWidth}px` );
    setProperty();
    new win.ResizeObserver( setProperty ).observe( $root );
})();

// `style="white-space: nowrap"`
registerFormatType( 'utilities-gutenberg/nowrap', {
    title: __( 'no line break', 'gutenberg' ),
    tagName: 'span',
    className: 'white-space-nowrap',
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
