import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { select, withSelect, withDispatch } from '@wordpress/data';
import { compose, withState } from '@wordpress/compose';
import { ToggleControl, TextControl } from '@wordpress/components';
import { headingLevel1 } from "@wordpress/icons";

const TitleSettings = ( { setState, updateMeta, ...props } ) => (
    <>
        <ToggleControl
            label={ __( 'Hide page title', 'title' ) }
            help={ __( 'The title will still be visible for screen readers.', 'title' ) }
            checked={ !!props._hideTitle }
            onChange={ state => {
                updateMeta( '_hidetitle', state )
                setState()
            } }
        />
        <TextControl
            label={ __( 'Subtitle', 'title' ) }
            help={ __( 'A subtitle is theme dependent and requires implementation in templates. Otherwise it will be ignored.', 'title' ) }
            value={ props._subtitle }
            onChange={ subtitle => {
                updateMeta( '_subtitle', subtitle )
                setState()
            } }
        />
        <p><b>{ __( 'Good to know:', 'title' ) }</b></p>
        <ul className="description">
            <li>{ __( 'A double space in title will result in a linebreak on frontend.', 'title' ) }</li>
            <li>{ __( 'If you use a heading block level 1 in the editor the title on top of the page will be removed completely by default.', 'title' ) }</li>
        </ul>
    </>
)

const TitleControl = compose( [
    withState(),
    withSelect( () => ( {
        // title: select("core/editor").getEditedPostAttribute( 'title' )||'',
        _hideTitle: select( 'core/editor' ).getEditedPostAttribute( 'meta' )['_hidetitle']||false,
        _subtitle: select( 'core/editor' ).getEditedPostAttribute( 'meta' )['_subtitle']||''
    } ) ),
    withDispatch( ( dispatch ) => ( {
        updateMeta( key, value ) {
            let meta = {}
            meta[key] = value

            dispatch('core/editor').editPost( { meta } )
        }
    } ) )
] )( TitleSettings )

const TitlePanel = () => {
    return (
        <PluginDocumentSettingPanel
            title={__('Title settings', 'title')}
            className="edit-title-panel"
            icon={ headingLevel1 }>
            <TitleControl />
        </PluginDocumentSettingPanel>
    )
}

registerPlugin( 'title-panel', {
    render: TitlePanel,
    icon: headingLevel1
} );