import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import { Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, Flex, FlexItem, FocalPointPicker,
    __experimentalNumberControl as NumberControl } from "@wordpress/components";
import { createHigherOrderComponent } from '@wordpress/compose';
import { useState } from 'react';
import { select } from "@wordpress/data";

// restrict to specific block names
const allowedBlocks = ['core/image'];

/**
 * Add custom attribute for mobile visibility.
 *
 * @param {Object} settings
 * @param {String} name
 *
 * @return {Object} modified settings
 */
function addAttributes( settings, name ) {
    if ( allowedBlocks.includes( name ) ) settings = {
        ...settings,
        attributes: {
            ...settings.attributes,
            focalPoint: {
                type: 'object',
                default: {
                    x: 0.5,
                    y: 0.5,
                }
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

        if ( !allowedBlocks.includes( props.name ) )
            return <BlockEdit {...props } />

        const {
            attributes,
            setAttributes,
            isSelected,
        } = props;

        const {
            width, height, url,
            aspectRatio,
            focalPoint
        } = attributes;

        const [aspectRatioX, aspectRatioY] = (aspectRatio || '/').split( '/' )

        const [ _focalPoint, setFocalPoint ] = useState( focalPoint );

        const disabled = width && width !== 'auto' && height;

        return (
            <Fragment>
                <BlockEdit {...props } />
                { isSelected && aspectRatio && <InspectorControls>
                    <PanelBody className="aspect-ratio-settings">
                        <p className={ 'label' }>{ __( 'Aspect ratio' ) }</p>
                        <Flex style={ { paddingBottom: '8px' } }>
                            <FlexItem>
                                <NumberControl
                                    // label={ __( 'Aspect ratio X' ) }
                                    placeholder={ __( 'Width' ) }
                                    value={ aspectRatioX }
                                    disabled={ disabled }
                                    spinControls={ 'none' }
                                    onChange={ aspectRatioX => {
                                        const aspectRatio = `${aspectRatioX}/${aspectRatioY}`;
                                        setAttributes( { aspectRatio } )
                                    } }
                                />
                            </FlexItem>
                            <FlexItem style={ { paddingBottom: '8px' } }>/</FlexItem>
                            <FlexItem>
                                <NumberControl
                                    // label={ __( 'Aspect ratio Y' ) }
                                    placeholder={ __( 'Height' ) }
                                    value={ aspectRatioY }
                                    disabled={ disabled }
                                    spinControls={ 'none' }
                                    onChange={ aspectRatioY => {
                                        const aspectRatio = `${aspectRatioX}/${aspectRatioY}`;
                                        setAttributes( { aspectRatio } )
                                    } }
                                />
                            </FlexItem>
                        </Flex>
                        <FocalPointPicker
                            __nextHasNoMarginBottom
                            label={ __( 'Set focal point', 'aspect-ratio' ) }
                            url={ url }
                            value={ focalPoint }
                            onDragStart={ focalPoint => {
                                setAttributes( { focalPoint } )
                                setFocalPoint( focalPoint )
                            } }
                            onDrag={ focalPoint => {
                                setAttributes( { focalPoint } )
                                setFocalPoint( focalPoint )
                            } }
                            onChange={ focalPoint => {
                                setAttributes( { focalPoint } )
                                setFocalPoint( focalPoint )
                            } }
                        />
                    </PanelBody>
                </InspectorControls> }
            </Fragment>
        );
    };
}, 'withAdvancedControls');

/**
 * Add custom element class in save element.
 *
 * TODO: add styles to `img` ... not to wrapper element
 *
 * @param {Object} extraProps     Block element.
 * @param {Object} blockType      Blocks object.
 * @param {Object} attributes     Blocks attributes.
 *
 * @return {Object} extraProps Modified block element.
 */

// add filters

addFilter(
    'blocks.registerBlockType',
    'utilities/aspect-ratio',
    addAttributes
);

addFilter(
    'editor.BlockEdit',
    'utilities/aspect-ratio-control',
    withAdvancedControls
);

addFilter(
    'blocks.getSaveContent.extraProps',
    'utilities/applyAspectRatioAttribute',
    ( extraProps, blockType, attributes ) => {
        if ( allowedBlocks.includes( blockType.name ) ) {
            const {
                width, height,
                aspectRatio,
                focalPoint
            } = attributes;

            if ( aspectRatio ) {
                // custom `width` and `height` is set
                // ... so `aspect-ratio` is overruled
                if ( width && width !== 'auto' && height ) {}
                // center is default
                else if ( focalPoint.x !== .5 || focalPoint.y !== .5 ) {
                    extraProps = {
                        ...extraProps,
                        'data-object-position': `${ focalPoint.x * 100 }% ${ focalPoint.y * 100 }%`,
                    }
                }
            }
        }

        return extraProps;
    }
);

const aspectRatioPreview = createHigherOrderComponent( ( BlockListBlock ) => {
    return ( props ) => {
        if ( allowedBlocks.includes( props.name ) ) {
            const $img = document.querySelector( `#block-${props.clientId} img` );
            if ( $img ) {
                const {
                    width, height,
                    aspectRatio,
                    scale,
                    focalPoint
                } = props.attributes;

                // reset
                $img.style.aspectRatio = '';
                $img.style.objectFit = ''
                $img.style.objectPosition = ''
                $img.style.width = '';
                $img.style.height = '';
                $img.parentElement.style.height = '';
                // show resize handles
                if ( $img.nextElementSibling ) $img.nextElementSibling.style.display = ''

                if ( aspectRatio ) {
                    // custom `width` and `height` is set
                    // ... so `aspect-ratio` is overruled
                    if ( !width || width === 'auto' || !height ) {
                        window.requestAnimationFrame( () => {
                            $img.style.aspectRatio = aspectRatio;
                            $img.style.objectFit = scale || 'cover'
                            $img.style.objectPosition = `${ focalPoint.x * 100 }% ${ focalPoint.y * 100 }%`;

                            if ( width !== 'auto' ) {
                                $img.style.width = width || '100%';
                                $img.style.height = 'auto';
                                $img.parentElement.style.height = 'auto';
                            }
                            else {
                                $img.style.width = '';
                                $img.parentElement.style.width = '';
                            }

                            // no resize handles
                            if ( $img.nextElementSibling ) $img.nextElementSibling.style.display = 'none'
                        } )
                    }
                }
            }
        }

        return <BlockListBlock { ...props } />
    };
}, 'aspectRatioPreview' );

// update preview styles
wp.hooks.addFilter(
    'editor.BlockListBlock',
    'utilities/aspect-ratio-preview',
    aspectRatioPreview
);

// set aspect ratio select option to match current setting
addFilter(
    'blockEditor.useSetting.before',
    'utilities/aspect-ratio-select',
    ( settingValue, settingName, clientId, blockName ) => {
        if ( blockName === 'core/image' && settingName === 'dimensions.aspectRatios.theme' ) {
            const attributes = select('core/block-editor').getBlockAttributes( clientId );
            settingValue = [{
                "name": __( 'Manually', 'aspect-ratio' ),
                "slug": "manually",
                "ratio": attributes.aspectRatio || '/'
            }]
        }

        return settingValue;
    }
);