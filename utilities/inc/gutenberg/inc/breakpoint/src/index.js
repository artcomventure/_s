import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import { Fragment } from '@wordpress/element';
import { InspectorAdvancedControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from "@wordpress/components";
import { createHigherOrderComponent } from '@wordpress/compose';

import classnames from 'classnames';

// restrict to specific block names
const allowedBlocks = [];
const disallowedBlocks = [];

/**
 * Add custom attribute for mobile visibility.
 *
 * @param {Object} settings
 * @param {String} name
 *
 * @return {Object} modified settings
 */
function addAttributes( settings, name ) {
    // check if object exists for old Gutenberg version compatibility
    // add allowedBlocks restriction
    if ( (!allowedBlocks.length || allowedBlocks.includes( name )) && !disallowedBlocks.includes( name ) ) settings = {
        ...settings,
        attributes: {
            ...settings.attributes,
            breakpoint: {
                type: 'string',
                default: '',
            }
        },
    };

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
            breakpoint
        } = attributes;

        const BREAKPOINTS = {};
        const $wrapper = document.querySelector( '.editor-styles-wrapper' );
        if ( $wrapper ) {
            const CSS = getComputedStyle( $wrapper );
            ['xxs', 'xs', 'sm', 'md', 'lg', 'xl', 'xxl', 'mobile', 'desktop', 'content', 'wide'].forEach( size => {
                const value = CSS.getPropertyValue(`--width-${size}`);
                if ( !value ) return;

                BREAKPOINTS[size] = parseInt( value );
            } );
        }

        const options = [{ value: '', label: __( 'Default' ) }]
        Object.keys( BREAKPOINTS ).forEach( size => options.push( {
            value: size,
            label: `${BREAKPOINTS[size]}px (width-${size})`
        } ) );

        // check breakpoint
        if ( props.attributes.className ) {
            const match = props.attributes.className.match( new RegExp( `(?:^| )width-([^ $]*)(?: |$)` ) );
            if ( match && match[1] !== breakpoint ) {
                setAttributes( { breakpoint: !!BREAKPOINTS[match[1]] ? match[1] : '' } )
            }
        }

        return (
            <Fragment>
                <BlockEdit {...props} />
                { isSelected && (!allowedBlocks.length || allowedBlocks.includes( name )) && !disallowedBlocks.includes( name ) &&
                    <InspectorAdvancedControls>
                        <PanelBody className="breakpoint-settings">
                            <SelectControl
                                label={ __( 'Apply predefined width', 'breakpoint' ) }
                                help={ __( 'You can also set custom sizes by adding a class `width-PX`.', 'breakpoint' ) }
                                value={ breakpoint }
                                options={ options }
                                onChange={ size => {
                                    let className = props.attributes.className || '';

                                    // remove old one
                                    if ( breakpoint !== size )
                                        className = className.replace( new RegExp( `(?:^| )width-${breakpoint}(?: |$)` ), ' ' ).trim()

                                    // set new
                                    if ( size ) className = classnames( className, `width-${size}` )

                                    if ( className !== (props.attributes.className || '') )
                                        setAttributes( { className } )

                                    setAttributes( { breakpoint: size } )
                                } }
                                __nextHasNoMarginBottom
                            />
                        </PanelBody>
                    </InspectorAdvancedControls>
                }
            </Fragment>
        );
    };
}, 'withAdvancedControls');

// add filters

addFilter(
    'blocks.registerBlockType',
    'utilities/breakpoint',
    addAttributes
);

addFilter(
    'editor.BlockEdit',
    'utilities/breakpoint-control',
    withAdvancedControls
);