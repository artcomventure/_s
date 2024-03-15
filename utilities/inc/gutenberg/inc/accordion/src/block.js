import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from "@wordpress/components";
import { compose, withState } from '@wordpress/compose';
import { pullquote, redo } from "@wordpress/icons";

const AccordionEdit = ( { attributes, setAttributes, ...props } ) => ( <>
    <InspectorControls>
        <PanelBody className="accordion-settings-panel" >
            <ToggleControl
                label={ __( 'Multiple', 'accordion' ) }
                help={ __( 'Allow multiple items to be opened at once.', 'accordion' ) }
                checked={ attributes.multiple }
                onChange={ ( multiple ) => {
                    setAttributes( { multiple } )
                    if ( !multiple ) {
                        setAttributes( { openAll: false } )
                        setAttributes( { openFirst: true } )
                    }
                } }
            />
            <ToggleControl
                label={ __( 'All collapsible', 'accordion' ) }
                help={ __( 'Current behaviour:', 'accordion' ) + ' ' + (attributes.closeAll
                    ? __( 'allow to collapse all items.', 'accordion' ) : __( 'at least one item will be always open.', 'accordion' )) }
                checked={ attributes.closeAll }
                onChange={ ( closeAll ) => {
                    setAttributes( { closeAll } )
                    if ( !closeAll && !attributes.openAll ) setAttributes( { openFirst: true } )
                } }
            />
            <PanelBody title={ __( 'Loading behavior', 'accordion' ) } initialOpen={ false } icon={ redo }>
                <ToggleControl
                    label={ __( 'Open all', 'accordion' ) }
                    help={ __( 'All items are open when loading.', 'accordion' ) }
                    disabled={ !attributes.multiple }
                    checked={ attributes.multiple && attributes.openAll }
                    onChange={ ( openAll ) => {
                        setAttributes( { openAll } )
                        if ( openAll ) setAttributes( { openFirst: false } )
                        else if ( !attributes.closeAll ) setAttributes( { openFirst: true } )
                    } }
                />
                <ToggleControl
                    label={ __( 'Open first item', 'accordion' ) }
                    help={ __( 'Open first item when loading.', 'accordion' ) }
                    disabled={ attributes.openAll || !attributes.closeAll }
                    checked={ attributes.openFirst }
                    onChange={ ( openFirst ) => setAttributes( { openFirst } ) }
                />
            </PanelBody>
        </PanelBody>
    </InspectorControls>

    <InnerBlocks
        template={ [
            [ 'accordion/item', {} ],
        ] }
        allowedBlocks={ [ 'accordion/item'] } />
</> )

const AccordionControl = compose( [
    withState()
] )( AccordionEdit )

registerBlockType( 'accordion/widget', {
    title: __( 'Accordion', 'accordion' ),
    description: __( 'Collapsible content.', 'accordion' ),
    icon: pullquote,
    category: 'widgets',
    keywords: [ __( 'Accordion', 'accordion' ) ],
    // https://developer.wordpress.org/block-editor/reference-guides/block-api/block-supports/
    supports: {
        anchor: true,
        align: [ 'wide', 'full' ]
    },

    attributes: {
        multiple: {
            type: 'boolean',
            default: false
        },
        openAll: {
            type: 'boolean',
            default: false
        },
        closeAll: {
            type: 'boolean',
            default: false
        },
        openFirst: {
            type: 'boolean',
            default: true
        }
    },

    edit: ( props ) => {
        return <AccordionControl { ...props } />
    },

    save: ( { attributes, ...props } ) => {
        let className = [];
        if ( attributes.multiple ) className.push( 'allow-multiple' )
        if ( attributes.closeAll ) className.push( 'allow-close-all' )
        if ( attributes.openAll ) className.push( 'open-all' )
        else if ( attributes.openFirst ) className.push( 'open-first' )

        return <div className={ className.join( ' ' ) }>
            <InnerBlocks.Content />
        </div>
    }
} )

registerBlockType( 'accordion/item', {
    title: __( 'Item', 'accordion' ),
    parent: [ 'accordion/block' ],

    edit: ( props ) => ( <>
        <InnerBlocks
            template={ [
                [ 'core/heading' ],
                [ 'accordion/item-content', {} ],
            ] }
            templateLock="all" />
    </> ),

    save: function( { attributes, ...props } ) {
        return <div><InnerBlocks.Content /></div>
    }
} );

registerBlockType( 'accordion/item-content', {
    title: __( 'Content', 'accordion' ),
    parent: [ 'accordion/item' ],

    edit: ( props ) => ( <>
        <InnerBlocks
            template={ [
                [ 'core/paragraph' ]
            ] }
            templateLock={ false } />
    </> ),

    save: function( { attributes, ...props } ) {
        return <div><InnerBlocks.Content /></div>
    }
} );