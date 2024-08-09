import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import { Fragment } from '@wordpress/element';
import { InspectorAdvancedControls, __experimentalLinkControl as LinkControl } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from "@wordpress/components";
import { createHigherOrderComponent } from '@wordpress/compose';

import classnames from 'classnames';

// restrict to specific block names
const allowedBlocks = [];

/**
 * Add custom attribute for mobile visibility.
 *
 * @param {Object} settings Settings for the block.
 *
 * @return {Object} settings Modified settings.
 */
function addAttributes( settings ) {

    // check if object exists for old Gutenberg version compatibility
    // add allowedBlocks restriction
    if ( typeof settings.attributes !== 'undefined'
        && (!allowedBlocks.length || allowedBlocks.includes( settings.name ))
    ) settings.attributes = Object.assign( settings.attributes,
        {
            hideOnMobile: {
                type: 'boolean',
                default: false,
            }
        },
        {
            hideOnTablet: {
                type: 'boolean',
                default: false,
            }
        }, {
            hideOnDesktop: {
                type: 'boolean',
                default: false,
            }
        }
    );

    return settings;
}

/**
 * Add mobile visibility controls on Advanced Block Panel.
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
            setAttributes,
            isSelected,
        } = props;

        const {
            hideOnMobile,
            hideOnTablet,
            hideOnDesktop,
        } = attributes;

        const CSS = getComputedStyle( document.querySelector( '.editor-styles-wrapper' ) );
        const BREAKPOINTS = {};
        ['mobile', 'desktop'].forEach( size => {
            const value = CSS.getPropertyValue(`--width-${size}`);
            if ( !value ) return;

            BREAKPOINTS[size] = parseInt( value );
        } );

        return (
            <Fragment>
                <BlockEdit {...props} />
                { isSelected && (!allowedBlocks.length || allowedBlocks.includes( name )) &&
                    <InspectorAdvancedControls>
                        <PanelBody className="hide-settings">
                            { !!BREAKPOINTS.mobile && <ToggleControl
                                label={ __( 'Hide on mobile', 'hide' ) }
                                help={ sprintf( __( '%dpx width and narrower', 'hide' ), BREAKPOINTS.mobile ) }
                                checked={ hideOnMobile }
                                onChange={ hideOnMobile => setAttributes( { hideOnMobile } ) }
                            /> }
                            { !!BREAKPOINTS.mobile && !!BREAKPOINTS.desktop && <ToggleControl
                                label={ __( 'Hide on tablet', 'hide' ) }
                                help={ sprintf( __( 'between %dpx and %dpx width', 'hide' ), BREAKPOINTS.mobile, BREAKPOINTS.desktop ) }
                                checked={ hideOnTablet }
                                onChange={ hideOnTablet => setAttributes( { hideOnTablet } ) }
                            /> }
                            { !!BREAKPOINTS.desktop && <ToggleControl
                                label={ __( 'Hide on desktop', 'hide' ) }
                                help={ sprintf( __( '%dpx width and wider', 'hide' ), BREAKPOINTS.desktop ) }
                                checked={ hideOnDesktop }
                                onChange={ hideOnDesktop => setAttributes( { hideOnDesktop } ) }
                            /> }
                            <p>{ __( 'This settings only account for screen size not devices!', 'hide' ) }</p>
                        </PanelBody>
                    </InspectorAdvancedControls>
                }
            </Fragment>
        );
    };
}, 'withAdvancedControls');

/**
 * Add custom element class in save element.
 *
 * @param {Object} extraProps     Block element.
 * @param {Object} blockType      Blocks object.
 * @param {Object} attributes     Blocks attributes.
 *
 * @return {Object} extraProps Modified block element.
 */
function applyHideClasses( extraProps, blockType, attributes ) {

    const {
        hideOnMobile,
        hideOnTablet,
        hideOnDesktop,
    } = attributes;

    // allowedBlocks restriction
    if ( !allowedBlocks.length || allowedBlocks.includes( blockType.name ) ) {
        // add class(es)
        if ( hideOnMobile ) extraProps.className = classnames( extraProps.className, 'hide-mobile' );
        if ( hideOnTablet ) extraProps.className = classnames( extraProps.className, 'hide-tablet' );
        if ( hideOnDesktop ) extraProps.className = classnames( extraProps.className, 'hide-desktop' );
    }

    return extraProps;
}

// add filters

addFilter(
    'blocks.registerBlockType',
    'utilities/hide',
    addAttributes
);

addFilter(
    'editor.BlockEdit',
    'utilities/hide-control',
    withAdvancedControls
);

addFilter(
    'blocks.getSaveContent.extraProps',
    'utilities/applyHideClasses',
    applyHideClasses
);