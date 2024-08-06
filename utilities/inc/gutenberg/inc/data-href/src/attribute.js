import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import { Fragment } from '@wordpress/element';
import {
    InspectorAdvancedControls,
    __experimentalLinkControl as LinkControl
} from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from "@wordpress/components";
import { createHigherOrderComponent } from '@wordpress/compose';

// restrict to specific block names
const allowedBlocks = [];

/**
 * Add custom `data-href` attribute.
 *
 * @param {Object} settings Settings for the block.
 *
 * @return {Object} settings Modified settings.
 */
function addAttribute( settings ) {

    // check if object exists for old Gutenberg version compatibility
    // add allowedBlocks restriction
    if ( typeof settings.attributes !== 'undefined'
        && (!allowedBlocks.length || allowedBlocks.includes( settings.name ))
    ) settings.attributes = Object.assign( settings.attributes, {
        link:{
            type: 'object',
            default: {},
        }
    } );

    return settings;
}

/**
 * Add data-href control on Advanced Block Panel.
 *
 * @param {function} BlockEdit Block edit component.
 *
 * @return {function} BlockEdit Modified block edit component.
 */
const withAdvancedControls = createHigherOrderComponent( ( BlockEdit ) => {
    return ( props ) => {

        const {
            name,
            attributes,
            setAttributes
        } = props;

        const {
            link,
        } = attributes;

        return (
            <Fragment>
                <BlockEdit { ...props } />
                { (!allowedBlocks.length || allowedBlocks.includes( name )) &&
                    <InspectorAdvancedControls>
                        <PanelBody className="data-href-settings">
                            <label>{ __( 'Link', 'data-href' ) }</label>
                            <LinkControl
                                key="data-href"
                                value={ link }
                                settings={ [] }
                                onChange={ ( link ) => setAttributes( { link } ) }
                                onRemove={ () => setAttributes( { link: {} } ) }
                            />
                            <p>{ __( 'Link any element. Accessibility ready.', 'data-href' ) }</p>
                        </PanelBody>
                    </InspectorAdvancedControls>
                }
            </Fragment>
        );

    };
}, 'withAdvancedControls');

/**
 * Add attributes in save element.
 *
 * @param {Object} extraProps     Block element.
 * @param {Object} blockType      Blocks object.
 * @param {Object} attributes     Blocks attributes.
 *
 * @return {Object} extraProps Modified block element.
 */
function applyAttribute( extraProps, blockType, attributes ) {

    const { link } = attributes;

    // check if attribute exists for old Gutenberg version compatibility
    // add allowedBlocks restriction
    if ( typeof link !== 'undefined' && link.url && (!allowedBlocks.length || allowedBlocks.includes( blockType.name )) ) {
        extraProps['data-href'] = link.url;
        extraProps['role'] = 'link';
        extraProps['tabindex'] = 0;
        // extraProps['_target'] = '';
    }

    return extraProps;
}

// add filters

addFilter(
    'blocks.registerBlockType',
    'data-href/attribute',
    addAttribute
);

addFilter(
    'editor.BlockEdit',
    'data-href/attribute-control',
    withAdvancedControls
);

addFilter(
    'blocks.getSaveContent.extraProps',
    'data-href/applyAttribute',
    applyAttribute
);