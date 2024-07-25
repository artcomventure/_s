import {__} from '@wordpress/i18n';
import {registerPlugin} from '@wordpress/plugins';
import {PluginDocumentSettingPanel} from '@wordpress/edit-post';
import {select, withSelect, withDispatch} from '@wordpress/data';
import {compose, withState} from '@wordpress/compose';
import {TextControl} from '@wordpress/components';

const BodyclassSettings = ({setState, updateMeta, ...props}) => {

    return (
        <>
            <TextControl
                label={__('Extra <html> class(es)', 'bodyclass')}
                help={__('Separate multiple classes with spaces.')}
                value={props._htmlclass}
                onChange={(state) => {
                    updateMeta('_htmlclass', state)
                    setState()
                }}
            />
            <TextControl
                label={__('Extra <body> class(es)', 'bodyclass')}
                help={__('Separate multiple classes with spaces.')}
                value={props._bodyclass}
                onChange={(state) => {
                    updateMeta('_bodyclass', state)
                    setState()
                }}
            />
            <TextControl
                label={__('Extra <article> class(es)', 'bodyclass')}
                help={__('Separate multiple classes with spaces.')}
                value={props._articleclass}
                onChange={(state) => {
                    updateMeta('_articleclass', state)
                    setState()
                }}
            />
        </>
    );
}

const BodyclassControl = compose([
    withState(),
    withSelect(() => ({
        _htmlclass: select('core/editor').getEditedPostAttribute('meta')['_htmlclass'] || '',
        _bodyclass: select('core/editor').getEditedPostAttribute('meta')['_bodyclass'] || '',
        _articleclass: select('core/editor').getEditedPostAttribute('meta')['_articleclass'] || '',
    })),
    withDispatch((dispatch) => ({
        updateMeta(key, value) {

            let meta = {}
            meta[key] = value

            dispatch('core/editor').editPost({meta})
        }
    }))
])(BodyclassSettings)

const BodyclassPanel = () => {
    return (
        <PluginDocumentSettingPanel
            title={__('Page Class', 'bodyclass')}
            className="edit-bodyclass-panel">
            <BodyclassControl/>
        </PluginDocumentSettingPanel>
    )
}

registerPlugin('bodyclass-panel', {
    render: BodyclassPanel,
    bodyclass: ''
});
