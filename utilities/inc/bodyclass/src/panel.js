import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { select, withSelect, withDispatch } from '@wordpress/data';
import { compose, withState } from '@wordpress/compose';
import { TextControl } from '@wordpress/components';

const BodyclassSettings = ( { setState, updateMeta, ...props } ) => {

    return (
        <>
            <TextControl
                label={ __( 'Class name', 'bodyclass' ) }
                help={ __( 'This class will be added to the <body> tag', 'bodyclass' ) }
                value={ props._bodyclass }
                onChange={ ( state ) => {
                    updateMeta( '_bodyclass', state )
                    setState()
                } }
            />
        </>
    );
}

const BodyclassControl = compose( [
    withState(),
    withSelect( () => ( {
        _bodyclass: select( 'core/editor' ).getEditedPostAttribute( 'meta' )['_bodyclass']||'',
    } ) ),
    withDispatch( ( dispatch ) => ( {
        updateMeta( key, value ) {

            let meta = {}
            meta[key] = value

            dispatch('core/editor').editPost( { meta } )
        }
    } ) )
] )( BodyclassSettings )

const BodyclassPanel = () => {
    return (
        <PluginDocumentSettingPanel
            title={__('Page class', 'bodyclass')}
            className="edit-bodyclass-panel">
            <BodyclassControl />
        </PluginDocumentSettingPanel>
    )
}

registerPlugin( 'bodyclass-panel', {
    render: BodyclassPanel,
    bodyclass: ''
} );
