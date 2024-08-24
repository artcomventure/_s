import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, __experimentalNumberControl as NumberControl, SelectControl, TextControl } from "@wordpress/components";
import { withSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';
import { compose, withState } from '@wordpress/compose';
import { RawHTML } from "@wordpress/element";
import { postFeaturedImage } from "@wordpress/icons";

/**
 * List of block attributes.
 */
const blockAttributes = {
  post_type: {
    type: 'string',
    default: 'post'
  },
  posts_per_page: {
    type: 'string',
    default: '' // see TeaserListControl
  },
  orderby: {
    type: 'string',
    default: 'date'
  },
  columns: {
    type: 'string',
    default: '1'
  },
  more: {
    type: 'string',
    default: ''
  },
}

/**
 * Remove foreign, default or empty attributes.
 *
 * @param {Object} attributes
 * @returns {Object}
 */
const cleanUpBlockAttributes = attributes => {
  const attr = { ...attributes };

  Object.keys( attr ).map( ( attribute ) => {
    if ( !blockAttributes[attribute]
      || blockAttributes[attribute].default === attr[attribute] // is default value
      || attr[attribute] === '' // is empty
    ) delete attr[attribute]
  } )

  return attr;
}

// used for serverside render
registerBlockType( 'teaser-list/preview', {
  title: __( 'Teaser List', 'teaser-list' ),
  parent: [ 'teaser-list/block' ],
  attributes: blockAttributes,
} );

const TeaserListBlock = ( { post_types, attributes, setAttributes, ...props } ) => {
  const postTypeOptions = [];
  post_types.forEach( post_type => postTypeOptions.push( { value: post_type.slug, label: post_type.name } ) )

  const $block = document.getElementById( `block-${props.clientId}` );
  if ( $block ) $block.setAttribute( 'data-grid', attributes.columns );

  return (
    <>
      <ServerSideRender
        block="teaser-list/preview"
        attributes={ cleanUpBlockAttributes( attributes ) }
      />

      <InspectorControls>
        <PanelBody title={ __( 'Settings' ) } className="testimonial-settings-panel">
          <>
            <SelectControl
              label={ __( 'Post type' ) }
              value={ attributes.post_type }
              options={ postTypeOptions }
              onChange={ ( post_type ) => setAttributes( { post_type } ) }
            />
            <NumberControl
              label={ __( 'Number of posts', 'teaser-list' ) }
              min={ 1 }
              value={ attributes.posts_per_page }
              placeholder={ Block.attributes.posts_per_page.default }
              onChange={ ( posts_per_page ) => setAttributes( { posts_per_page } ) }
            />
            <SelectControl
              label={ __( 'Sort by', 'teaser-list' ) }
              value={ attributes.orderby }
              options={ [
                { value: 'date', label: __( 'Date' ) },
                { value: 'rand', label: __( 'random', 'teaser-list' ) }
              ] }
              onChange={ ( orderby ) => setAttributes( { orderby } ) }
            />
            <NumberControl
              label={ __( 'Columns' ) }
              min={ 1 }
              max={ 9 }
              value={ attributes.columns }
              placeholder={ Block.attributes.columns.default }
              onChange={ ( columns ) => setAttributes( { columns } ) }
            />
            <TextControl
              label={ __( 'Load more' ) }
              placeholder={ __( 'Button text' ) }
              value={ attributes.more }
              onChange={ ( more ) => setAttributes( { more } ) }
            />
          </>
        </PanelBody>
      </InspectorControls>
    </>
  )
}

const TeaserListControl = compose( [
  withState(),
  withSelect( ( select, props ) => {
    const { getPostTypes, getSite } = select( 'core' );

    // get `posts_per_page` setting
    blockAttributes.posts_per_page.default = (getSite() || {}).posts_per_page || 10;

    let post_types = (getPostTypes( { per_page: -1 } ) || []).filter( post_type => {
      return post_type.viewable
    } )

    return { post_types }
  } )
] )( TeaserListBlock );

const Block = registerBlockType( 'teaser-list/block', {
  title: __( 'Teaser List', 'teaser-list' ),
  description: __( 'Add grid of post teasers.', 'teaser-list' ),
  icon: postFeaturedImage,
  category: 'widgets',
  keywords: [ __( 'Testimonials', 'testimonial' ) ],
  supports: {
    align: ['wide', 'full'],
    color: { background: true }
  },

  attributes: blockAttributes,

  edit: ( props ) => ( <TeaserListControl { ...props } /> ),
  save: function( { attributes, ...props } ) {
    const attr = [];

    new URLSearchParams( cleanUpBlockAttributes( attributes ) ).forEach( ( value, key ) => {
      attr.push( `${key}="${value}"` )
    } )

    return <div
      data-grid={ attributes.columns }
      className={ `${attributes.post_type}-teaser-list wp-block-group-is-layout-grid` }
    ><div>
      <RawHTML>{ `[teaser-list ${ attr.join( ' ' ) }]` }</RawHTML>
    </div></div>
  }
} )