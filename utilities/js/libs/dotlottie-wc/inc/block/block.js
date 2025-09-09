import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, MediaUpload, MediaUploadCheck  } from '@wordpress/block-editor';
import { Button, PanelBody, ToggleControl, SelectControl, __experimentalNumberControl as NumberControl } from "@wordpress/components";
import { compose, withState } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';
import { useEffect, useRef } from '@wordpress/element';

const DotLottieEdit = ( { attributes, setAttributes, setState, ...props } ) => {
  const dotLottieRef = useRef();
  useEffect(() => {
    // TODO: I'm not quite sure if this works on changing the src
    if ( dotLottieRef.current.dotLottie.isLoaded ) {
      const size = dotLottieRef.current.dotLottie.animationSize()
      dotLottieRef.current.dotLottie.canvas.width = size.width
      dotLottieRef.current.dotLottie.canvas.height = size.height
      dotLottieRef.current.dotLottie.resize()
    }
  } );

  return (
    <>
      <InspectorControls>
        <PanelBody title={ __( 'DotLottie Settings', 'dotlottie' ) } >
          <MediaUploadCheck>
            <MediaUpload
              onSelect={ media => {
                setAttributes( { attachment_id: media.id } )
                setAttributes( { src: media.url } )
              } }
              allowedTypes={ ['application/zip'] }
              value={ attributes.attachment_id }
              render={ ( { open } ) => (
                <Button __next40pxdefaultsize variant="secondary" onClick={ open }>
                  { attributes.attachment_id ? __( 'Change file', 'dotlottie' ) : __( 'Select file', 'dotlottie' ) }
                </Button>
              )}
            />
          </MediaUploadCheck>
          <p className="components-base-control__help"><i>{ __( 'In the editor, the animation is always played automatically and looped for preview purposes.', 'dotlottie' ) }</i></p>
          <SelectControl
            label={ __( 'Start animation', 'dotlottie' ) }
            // help={ __( 'Automatically start the animation.', 'dotlottie' ) }
            disabled={ !attributes.src }
            value={ attributes.start }
            options={ [
              { label: __( 'on file load', 'dotlottie' ), value: 'autoplay' },
              { label: __( 'on click', 'dotlottie' ), value: 'click' },
              { label: __( 'on hover', 'dotlottie' ), value: 'mouseover' },
              { label: __( 'on custom event', 'dotlottie' ), value: 'custom' },
            ] }
            onChange={ start => setAttributes( { start } ) }
          />
          <ToggleControl
            label={ __( 'Loop', 'dotlottie' ) }
            // help={ __( 'Loop the animation.', 'dotlottie' ) }
            disabled={ !attributes.src }
            checked={ attributes.loop }
            onChange={ loop => setAttributes( { loop } ) }
          />
          {/*<NumberControl*/}
          {/*  label={ __( 'Speed', 'dotlottie' ) }*/}
          {/*  // help={ __( 'Playback speed.', 'dotlottie' ) }*/}
          {/*  disabled={ !attributes.src }*/}
          {/*  min={ 1 }*/}
          {/*  value={ DotLottieBlock.attributes.speed.default !== attributes.speed ? attributes.speed : '' }*/}
          {/*  placeholder={ DotLottieBlock.attributes.speed.default }*/}
          {/*  onChange={ speed => setAttributes( { speed: parseInt( speed ) || 1 } ) }*/}
          {/*/>*/}
          <ToggleControl
            label={ __( 'Lazy Loading', 'dotlottie' ) }
            help={ __( 'The file is only loaded when it is immediately needed.', 'dotlottie' ) }
            disabled={ !attributes.src }
            checked={ attributes.lazyload }
            onChange={ lazyload => setAttributes( { lazyload } ) }
          />
        </PanelBody>
      </InspectorControls>

      <dotlottie-wc { ...{ src: attributes.src, speed: attributes.speed } } autoplay loop ref={ dotLottieRef }></dotlottie-wc>
    </>
  )
}

const DotLottieControl = compose( [
  withState(),
  withSelect( ( select, props ) => {
    return {
      ...props
    }
  } )
] )( DotLottieEdit )

const DotLottieBlock = registerBlockType( 'dotlottie/wc', {
  title: __( 'DotLottie', 'dotlottie' ),
  description: __( 'Web component that allows you to easily embed Lottie animations.', 'dotlottie' ),
  icon: { src: <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 78.04 78.22">
      <path fill="#00ddb3" d="M58.29,0H19.74C8.84,0,0,8.86,0,19.79v38.64c0,10.93,8.84,19.79,19.74,19.79h38.55c10.9,0,19.74-8.86,19.74-19.79V19.79c0-10.93-8.84-19.79-19.74-19.79Z"/>
      <path fill="#fff" d="M59.31,17.52c-13.43,0-18.41,9.61-22.41,17.34l-2.61,4.94c-4.24,8.19-7.4,13.17-15.57,13.17-.51,0-1.01.1-1.48.29-.47.19-.89.48-1.25.84-.36.36-.64.79-.84,1.25-.19.47-.29.97-.29,1.48,0,1.03.41,2.01,1.13,2.73.72.72,1.7,1.13,2.73,1.13,13.44,0,18.42-9.61,22.42-17.34l2.61-4.94c4.24-8.19,7.41-13.17,15.57-13.17.51,0,1.01-.1,1.48-.29.47-.19.89-.48,1.25-.84.36-.36.64-.79.84-1.26.19-.47.29-.97.29-1.48,0-1.03-.41-2.01-1.13-2.73-.72-.73-1.71-1.13-2.73-1.13Z"/>
    </svg> },
  category: 'media',
  // https://developer.wordpress.org/block-editor/reference-guides/block-api/block-supports/
  supports: {
    align: [ 'wide', 'full' ],
    anchor: true
  },

  attributes: {
    attachment_id: {
      type: 'number',
      default: ''
    },
    src: {
      type: 'string',
      default: ''
    },
    start: {
      type: 'string',
      default: 'autoplay'
    },
    loop: {
      type: 'boolean',
      default: false
    },
    speed: {
      type: 'number',
      default: 1
    },
    mode: {
      type: 'string',
      default: 'forward'
    },
    backgroundColor: {
      type: 'string',
      default: ''
    },
    lazyload: {
      type: 'boolean',
      default: true
    },
  },

  edit: ( props ) => {
    return <DotLottieControl { ...props } />
  },

  save: function( { attributes } ) {

    const attr = { 'data-src':  attributes.src }
    // lazy load
    if ( attributes.lazyload ) attr['data-src'] = attributes.src
    else attr.src = attributes.src

    if ( attributes.start === 'autoplay' ) { attr.autoplay = '' }
    else attr['data-start'] = attributes.start

    if ( attributes.loop ) { attr.loop = '' }

    // if ( attributes.speed && attributes.speed !== 1 ) { attr.speed = attributes.speed }

    return <dotlottie-wc {...attr}></dotlottie-wc>
  }
} );
