import { __, sprintf } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import { Fragment } from '@wordpress/element';
import { InspectorAdvancedControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from "@wordpress/components";
import { createHigherOrderComponent, compose, withState } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';

import classnames from 'classnames';

// restrict to specific block names
const allowedBlocks = [];
const disallowedBlocks = ['core/shortcode', 'core/post-title'];

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
            hideOnMobile: {
                type: 'boolean',
                default: false,
            },
            hideOnTablet: {
                type: 'boolean',
                default: false,
            },
            hideOnDesktop: {
                type: 'boolean',
                default: false,
            },
            srOnly: {
                type: 'boolean',
                default: false,
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
            hideOnMobile,
            hideOnTablet,
            hideOnDesktop,
            srOnly,
        } = attributes;

        const BREAKPOINTS = {};
        const $wrapper = document.querySelector( '.editor-styles-wrapper' );
        if ( $wrapper ) {
            const CSS = getComputedStyle( $wrapper );
            ['mobile', 'desktop'].forEach( size => {
                const value = CSS.getPropertyValue(`--width-${size}`);
                if ( !value ) return;

                BREAKPOINTS[size] = parseInt( value );
            } );
        }

        // check classes and set attributes accordingly
        if ( props.attributes.className ) {
            ['Mobile', 'Tablet', 'Desktop'].forEach( device => {
                const match = props.attributes.className.match( new RegExp( `(?:^| )hide-${device.toLowerCase()}(?: |$)` ) );
                const attribute = {}
                attribute[`hideOn${device}`] = !!match
                setAttributes( attribute )
            } )
        }

        return (
            <Fragment>
                <BlockEdit {...props} />
                { isSelected && (!allowedBlocks.length || allowedBlocks.includes( name )) && !disallowedBlocks.includes( name ) &&
                    <InspectorAdvancedControls>
                        <PanelBody className="hide-settings">
                            <ToggleControl
                                label={ __( 'Hide on mobile', 'hide' ) }
                                help={ !!BREAKPOINTS.mobile
                                    ? sprintf( __( '%dpx width and narrower', 'hide' ), BREAKPOINTS.mobile ) : '' }
                                checked={ hideOnMobile }
                                onChange={ hideOnMobile => {
                                    let className = props.attributes.className || '';
                                    // toggle class
                                    if ( hideOnMobile ) className = classnames( className, 'hide-mobile' );
                                    else className = className.replace( new RegExp( `(?:^| )hide-mobile(?: |$)` ), ' ' ).trim()

                                    setAttributes( { className } )
                                    setAttributes( { hideOnMobile } )
                                } }
                            />
                            <ToggleControl
                                label={ __( 'Hide on tablet', 'hide' ) }
                                help={ (!!BREAKPOINTS.mobile && !!BREAKPOINTS.desktop)
                                    ? sprintf( __( 'between %dpx and %dpx width', 'hide' ), BREAKPOINTS.mobile, BREAKPOINTS.desktop ) : '' }
                                checked={ hideOnTablet }
                                onChange={ hideOnTablet => {
                                    let className = props.attributes.className || '';
                                    // toggle class
                                    if ( hideOnTablet ) className = classnames( className, 'hide-tablet' );
                                    else className = className.replace( new RegExp( `(?:^| )hide-tablet(?: |$)` ), ' ' ).trim()

                                    setAttributes( { className } )
                                    setAttributes( { hideOnTablet } )
                                } }
                            />
                            <ToggleControl
                                label={ __( 'Hide on desktop', 'hide' ) }
                                help={ !!BREAKPOINTS.desktop
                                    ? sprintf( __( '%dpx width and wider', 'hide' ), BREAKPOINTS.desktop ) : '' }
                                checked={ hideOnDesktop }
                                onChange={ hideOnDesktop => {
                                    let className = props.attributes.className || '';
                                    // toggle class
                                    if ( hideOnDesktop ) className = classnames( className, 'hide-desktop' );
                                    else className = className.replace( new RegExp( `(?:^| )hide-desktop(?: |$)` ), ' ' ).trim()

                                    setAttributes( { className } )
                                    setAttributes( { hideOnDesktop } )
                                } }
                            />
                            <ToggleControl
                                label={ __( 'Screen reader only', 'hide' ) }
                                help={ __( 'Hidden but visible for screen readers', 'hide' ) }
                                checked={ srOnly }
                                onChange={ srOnly => setAttributes( { srOnly } ) }
                            />
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
function applySROnlyClass( extraProps, blockType, attributes ) {

    const {
        srOnly
    } = attributes;

    // allowedBlocks restriction
    if ( (!allowedBlocks.length || allowedBlocks.includes( blockType.name )) && !disallowedBlocks.includes( blockType.name ) ) {
        if ( srOnly ) extraProps.className = classnames( extraProps.className, 'screen-reader-text' );
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
    'utilities/applySROnlyClass',
    applySROnlyClass
);

// ---
// Hide post thumbnail.

const HideThumbnailField = ( { ...props } ) => {
    return (
        <ToggleControl
            label={ __( "Don't display featured image in template", 'hide' ) }
            help={ __( "This setting is theme dependent and usually only affects the single view display.", 'hide' ) }
            checked={ props.hide_thumbnail }
            onChange={ status => {
                props.updateMeta( status );
                props.setState()
            } }
        />
    )
}

const HideThumbnailControl = compose( [
    withState(),
    withSelect( ( select, props ) => ( {
        hide_thumbnail: select( 'core/editor' ).getEditedPostAttribute( 'meta' )['_hide_thumbnail']||false
    } ) ),
    withDispatch( ( dispatch ) => ( {
        updateMeta( value, prop ) {
            dispatch( 'core/editor' ).editPost(
                { meta: { '_hide_thumbnail': value } }
            );
        }
    } ) )
] )( HideThumbnailField );

const hideThumbnail = function( OriginalComponent ) {
    return ( props ) => {
        return [
            createElement( OriginalComponent, props ), // original featured image box
            <HideThumbnailControl />
        ];
    }
}

addFilter(
    'editor.PostFeaturedImage',
    'utilities/hide-featured-image',
    hideThumbnail
);