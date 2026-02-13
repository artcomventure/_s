import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import { BaseControl, Button, CheckboxControl, ComboboxControl, Flex, FlexBlock, FlexItem, Icon, PanelBody, SelectControl, TextControl, ToggleControl,
  __experimentalNumberControl as NumberControl,
  __experimentalToggleGroupControl as ToggleGroupControl,
  __experimentalToggleGroupControlOption as ToggleGroupControlOption
} from "@wordpress/components";
import { withSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';
import { compose, withState } from '@wordpress/compose';
import { RawHTML, useEffect } from "@wordpress/element";
import { arrowDown, arrowUp, close, postFeaturedImage, redo } from "@wordpress/icons";

// object to string
function encodeObject( object ) {
  if ( typeof object === 'object' && object !== null )
    object = encodeURIComponent( JSON.stringify( object ) )

  return object;
}

// string to object
function decodeObject( string ) {
  let object;

  try {
    object = decodeURIComponent(  string )
    object = JSON.parse( object )
  }
  catch ( e ) { object = {} }

  return object
}

/**
 * List of block attributes.
 */
const blockAttributes = {
  blockId: {
    type: 'string'
  },
  post_type: {
    type: 'string',
    default: 'post'
  },
  post__in: {
    type: 'array',
    default: []
  },
  post__not_in: {
    type: 'array',
    default: []
  },
  // although this is an object it must be saved as a string
  // ... because Gutenberg has _problems_ with multidimensional objects (thinkingface)
  filter: {
    type: 'string',
    default: '{}'
  },
  posts_per_page: {
    type: 'string',
    default: ''
  },
  orderby: {
    type: 'string',
    default: 'date'
  },
  theme: {
    type: 'string',
    default: 'grid'
  },
  group_by_month: {
    type: 'boolean',
    default: false
  },
  columns: {
    type: 'string',
    default: '1'
  },
  empty: {
    type: 'string',
    default: ''
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
    if ( ['post__in', 'post__not_in'].indexOf( attribute ) >= 0 ) {
      attr[attribute] = attr[attribute].filter( post_id => post_id )
    }

    if ( !blockAttributes[attribute]
      || blockAttributes[attribute].default === attr[attribute] // is default value
      || attr[attribute] === '' // is empty
    ) delete attr[attribute]
  } )

  return attr;
}

// used for serverside render
registerBlockType( 'posts-list/preview', {
  title: __( 'Posts List', 'posts-list' ),
  parent: [ 'posts-list/block' ],
  attributes: blockAttributes,
} );

const PostsListBlock = ( { post_types, posts, taxonomies, attributes, setAttributes, ...props } ) => {
  const {
    blockId,
    post_type,
    post__in,
    post__not_in,
    posts_per_page,
    group_by_month,
    theme,
    columns,
    orderby,
    empty,
    more
  } = attributes;

  useEffect( () => {
    if ( !blockId ) setAttributes( { blockId: props.clientId } );
  } );

  // saved as a string but actually an object
  const filter = decodeObject( attributes.filter )

  // current selected post type
  const post_type_object = post_types.filter( post_type_object => {
    return post_type_object.slug === post_type
  } )[0] || post_types[0] || {}

  // make sure post__in IDs are available
  if ( posts.length ) {
    let postIn = [...post__in]
    postIn = postIn.filter( post_id => {
      return !post_id || Object.values( posts ).some( option => option.value === post_id );
    } )
    if ( post__in.length !== postIn.length ) setAttributes( { post__in: postIn } )
  }

  // make sure post__not_in IDs are available
  if ( posts.length ) {
    let postNotIn = [...post__not_in]
    postNotIn = postNotIn.filter( post_id => {
      return !post_id || Object.values( posts ).some( option => option.value === post_id );
    } )
    if ( post__not_in.length !== postNotIn.length ) setAttributes( { post__not_in: postNotIn } )
  }

  const $block = document.getElementById( `block-${props.clientId}` );
  if ( $block ) $block.setAttribute( 'data-grid', attributes.columns );

  return (
    <>
      <ServerSideRender
        block="posts-list/preview"
        attributes={ cleanUpBlockAttributes( attributes ) }
        className={ `${props.className} ${post_type}-posts-list is-layout-${theme || blockAttributes.theme.default}` }
      />

      <InspectorControls>
        <PanelBody title={ __( 'Settings' ) } className="posts-list-settings-panel">
          <>
            <SelectControl
              label={ __( 'Post type' ) }
              value={ post_type }
              options={ post_types.map( post_type => ({ value: post_type.slug, label: post_type.name }) ) }
              onChange={ ( post_type ) => {
                setAttributes( { post_type } )
                setAttributes( { filter: '{}' } )
                setAttributes( { post__in: [] } )
                setAttributes( { post__not_in: [] } )
              } }
            />

            { !!posts.length && <BaseControl label={ __( 'Show specific %s', 'posts-list' ).replace( '%s', !!post_type_object.labels ? post_type_object.labels.name : __( 'Posts' ) ) }>
              { post__in.map( ( post_id, i ) => <Flex gap={ 0 }>
                <FlexBlock><ComboboxControl
                  value={ post_id }
                  options={ posts }
                  placeholder={ __( 'Search for %s to tease …', 'posts-list' ).replace( '%s', !!post_type_object.labels ? post_type_object.labels.singular_name : __( 'Post' ) ) }
                  onChange={ post_id => {
                    if ( post_id === null ) post__in.splice( i, 1 )
                    else post__in[i] = post_id

                    setAttributes( { post__in: [...post__in] } )
                  } }
                /></FlexBlock>

                { post__in.length > 1 && <Button size="small" className="order-post" iconSize={ 12 } icon={ arrowDown }
                  disabled={ post__in.length <= i + 1 }
                  onClick={ () => {
                    const postIn = [...post__in]
                    const post = postIn[i];
                    postIn.splice( i, 1 );
                    postIn.splice( i + 1, 0, post );
                    setAttributes( { post__in: postIn } )
                  } } /> }

                { post__in.length > 1 && <Button size="small" className="order-post" iconSize={ 12 } icon={ arrowUp }
                    disabled={ !i }
                    onClick={ () => {
                      const postIn = [...post__in]
                      const post = postIn[i];
                      postIn.splice( i, 1 );
                      postIn.splice( i - 1, 0, post );
                      setAttributes( { post__in: postIn } )
                    } } /> }

                <Button size="small" className="order-post" iconSize={ 12 } icon={ close }
                  onClick={ () => {
                    const postIn = [...post__in]
                    postIn.splice( i, 1 );
                    setAttributes( { post__in: postIn } )
                  } } />
              </Flex> ) }

              <Button
                  variant="secondary"
                  size="small"
                  onClick={ () => {
                    const postIn = [...post__in]
                    postIn.push( 0 )
                    setAttributes( { post__in: postIn } )
                  } }
              >
                { __( 'add %s', 'posts-list' ).replace( '%s', !!post_type_object.labels ? post_type_object.labels.singular_name : __( 'Post' ) ) }
              </Button>
            </BaseControl> }

            { !post__in.filter( value => value ).length && !!Object.keys( taxonomies ).length && !!(post_type_object.taxonomies || []).length &&
              <PanelBody title={ __( 'Filter by', 'posts-list' ) } className="posts-list-filter"
                         initialOpen={ false } icon={ redo }>

                <p className="description">
                  { __( 'Terms are filtered with OR within a taxonomy and with AND between taxonomies.', 'posts-list' ) }
                </p>

                { post_type_object.taxonomies.map( taxonomy => !!taxonomies[taxonomy] && <BaseControl
                  label={ taxonomies[taxonomy].labels.name || taxonomy }
                >
                  { taxonomies[taxonomy].terms.map( term => {
                    return (<ToggleGroupControl
                      label={ term.name }
                      isDeselectable
                      value={ !!filter[taxonomy]
                        ? (!!filter[taxonomy]['IN'] && filter[taxonomy]['IN'].indexOf( term.id ) >= 0
                            ? 'IN' : (!!filter[taxonomy]['NOT IN'] && filter[taxonomy]['NOT IN'].indexOf( term.id ) >= 0
                                ? 'NOT IN' : ''
                            )
                        ) : '' }
                      onChange={ operator => {
                        // remove term from filter
                        if ( !!filter[taxonomy] ) ['IN', 'NOT IN'].forEach( operator => {
                          if ( !filter[taxonomy][operator] ) return;
                          const index = filter[taxonomy][operator].indexOf( term.id )
                          if ( index >= 0 ) delete filter[taxonomy][operator][index]
                        } )

                        // add term to filter
                        if ( operator ) {
                          if ( !filter[taxonomy] ) filter[taxonomy] = {}
                          if ( !filter[taxonomy][operator] ) filter[taxonomy][operator] = []

                          filter[taxonomy][operator].push( term.id )
                        }

                        // clean up
                        Object.keys( filter ).forEach( taxonomy => {
                          Object.keys( filter[taxonomy] ).forEach( operator => {
                            filter[taxonomy][operator] = Object.values( filter[taxonomy][operator].filter( term_id => term_id ) )
                            if ( !filter[taxonomy][operator].length ) delete filter[taxonomy][operator];
                          } )

                          if ( !Object.keys( filter[taxonomy] ).length ) delete filter[taxonomy];
                        } )

                        // !!! mutated filter objects doesn't save !!! ... so we _clone_ it
                        setAttributes( { filter: encodeObject( { ...{}, ...filter } ) } )
                      } }
                    >
                      <ToggleGroupControlOption value="IN" label={ __( '+', 'posts-list' ) } />
                      <ToggleGroupControlOption value="NOT IN" label={ __( '-', 'posts-list' ) } />
                    </ToggleGroupControl>)
                  } ) }
                </BaseControl> ) }

              </PanelBody>
            }

            <TextControl
              label={ __( 'Theme', 'posts-list' ) }
              value={ theme !== blockAttributes.theme.default ? theme : '' }
              placeholder={ blockAttributes.theme.default }
              onChange={ ( theme ) => setAttributes( { theme } ) }
            />

            <ToggleControl
              label={ __( 'Group by month', 'posts-list' ) }
              className="hidden"
              checked={ group_by_month }
              onChange={ group_by_month => setAttributes( { group_by_month } ) }
            />

            <BaseControl><Flex align="start">
              <FlexBlock>
                <NumberControl
                  label={ __( 'Number of posts', 'posts-list' ) }
                  // help={ __( 'Enter "-1" for all.', 'posts-list' ) }
                  min={ -1 }
                  value={ posts_per_page }
                  placeholder={ blockAttributes.posts_per_page.default }
                  onChange={ ( posts_per_page ) => setAttributes( { posts_per_page } ) }
                />
              </FlexBlock>
              <FlexBlock>
                <NumberControl
                  label={ __( 'Columns' ) }
                  min={ 1 }
                  max={ 9 }
                  value={ columns }
                  placeholder={ blockAttributes.columns.default }
                  onChange={ ( columns ) => setAttributes( { columns } ) }
                  help={ __( 'This setting is theme-dependent.', 'posts-list' ) }
                />
              </FlexBlock>
            </Flex></BaseControl>

            { <SelectControl
              label={ __( 'Sort by', 'posts-list' ) }
              value={ orderby }
              options={ [
                { value: 'date', label: __( 'Date' ) },
                { value: 'menu_order', label: __( 'Order' ) },
                { value: 'title', label: __( 'Title' ) },
                { value: 'rand', label: __( 'random', 'posts-list' ) },
              ] }
              onChange={ ( orderby ) => setAttributes( { orderby } ) }
            /> }

            <TextControl
              label={ __( 'No entries message', 'posts-list' ) }
              placeholder={ __( 'No entries were found.', 'posts-list' ) }
              value={ empty }
              onChange={ ( empty ) => setAttributes( { empty } ) }
            />

            <TextControl
              label={ __( 'Load more' ) }
              help={ __( 'If there are less results than the setting on "Number of posts", the button is not added.', 'posts-list' ) }
              placeholder={ __( 'Button text' ) }
              value={ more }
              onChange={ ( more ) => setAttributes( { more } ) }
            />
          </>
        </PanelBody>
      </InspectorControls>
    </>
  )
}

const PostsListControl = compose( [
  withState(),
  withSelect( ( select, ownProps, props ) => {
    const {
      getPostTypes,
      getSite,
      getEntityRecords,
      getTaxonomy
    } = select( 'core' );

    const { attributes } = ownProps;

    // get `posts_per_page` setting and set as default
    blockAttributes.posts_per_page.default = (getSite() || {}).posts_per_page || 10;

    // get all post types
    let post_types = (getPostTypes( { per_page: -1 } ) || [])
    // ... which are _viewable_
    // post_types = post_types.filter( post_type => post_type.viewable );
    // ... and not WP special
    post_types = post_types.filter( post_type => [
      'attachment',
      'nav_menu_item',
      'wp_block',
      'wp_navigation',
      'wp_template',
      'wp_template_part',
      'wp_global_styles',
      'wp_font_family',
      'wp_font_face'
    ].indexOf( post_type.slug ) < 0 );

    // get all post of corresponding post type
    const posts = (getEntityRecords( 'postType', attributes.post_type, {
      per_page: -1,
      status : 'any',
      orderby: 'title',
      order: 'asc',
      exclude: select('core/editor').getCurrentPostId()
    } )||[]).map( post => {
      return ( {
        value: post.id,
        label: post.title.rendered || post.title.raw || __( 'Untitled' ),
      } )
    } )

    // load taxonomies with terms
    const taxonomies = {};
    post_types.forEach( post_type => {
      post_type.taxonomies.forEach( taxonomy => {
        const terms = getEntityRecords( 'taxonomy', taxonomy, { per_page: 100 } );
        if ( terms && terms.length ) taxonomies[taxonomy] = {
          ...{
            labels: {},
            terms
          }, // get terms
          ...(getTaxonomy( taxonomy ) || {}) // get taxonomy object
        }
      } )
    } )

    return {
      post_types,
      posts,
      taxonomies
    }
  } )
] )( PostsListBlock );

registerBlockType( 'posts-list/block', {
  title: __( 'Posts List', 'posts-list' ),
  description: __( 'Add grid of posts.', 'posts-list' ),
  icon: postFeaturedImage,
  category: 'widgets',
  keywords: [],
  supports: {
    anchor: true,
    align: ['wide', 'full'],
    color: { background: true }
  },

  attributes: blockAttributes,

  edit: ( props ) => ( <PostsListControl { ...props } /> ),
  save: function( { attributes, ...props } ) {
    const attr = [];
    new URLSearchParams( cleanUpBlockAttributes( attributes ) ).forEach( ( value, key ) => {
      if ( ['blockId'].indexOf( key ) >= 0 ) return;
      attr.push( `${key}="${value}"` )
    } )

    return <div
      data-uuid={ attributes.blockId }
      data-grid={ attributes.columns }
      className={ `${attributes.post_type}-posts-list wp-block-group is-layout-${attributes.theme || blockAttributes.theme.default}` }
    >
      <RawHTML>{ `[posts-list ${ attr.join( ' ' ) }]` }</RawHTML>
    </div>
  }
} )