import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import { Fragment } from '@wordpress/element';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button, ResponsiveWrapper, Flex, Spinner } from "@wordpress/components";
import { createHigherOrderComponent, compose, withState } from '@wordpress/compose';
import { select, useSelect, withSelect, withDispatch } from '@wordpress/data';
import {position} from "../../../../../../../../wp-includes/js/codemirror/csslint";

window.addEventListener( 'DOMContentLoaded', () => {
    const labels = {
        '#menu-item-cancel': `← ${__( 'Cancel' )}`,
        '#menu-item-gallery-edit': __( 'Edit Featured Images', 'featured-images' ),
        '#menu-item-gallery-library': __( 'Add to Featured Images', 'featured-images' ),
        // '.media-button-insert': __( 'Update Featured Images', 'featured-images' ),
    }

    new MutationObserver( mutations => {
        mutations.forEach( mutation => {
            if ( mutation.type !== 'childList') return;

            const $mediaModal = document.querySelector( '.media-frame-thumbnails' );
            if ( !$mediaModal ) return;

            Object.keys( labels ).forEach( selector => {
                const $element = $mediaModal.querySelector( selector );
                if ( !$element ) return;

                if ( $element.textContent !== labels[selector] )
                    $element.textContent = labels[selector];
            } )

            const $title = $mediaModal.querySelector( '#media-frame-title h1' );
            if ( $title ) {
                let $activeMenuItem = $mediaModal.querySelector( '.media-menu-item.active' )
                if ( $activeMenuItem && $title.textContent !== $activeMenuItem.textContent )
                    $title.textContent = $activeMenuItem.textContent;
            }

            const $insert = $mediaModal.querySelector( '.media-button-insert' );
            if ( $insert ) {
                const buttonText = $mediaModal.querySelector( '#menu-item-gallery-library.active' )
                    ? __( 'Add to Featured Images', 'featured-images' ) : __( 'Update Featured Images', 'featured-images' )

                if ( $insert.textContent !== buttonText )
                    $insert.textContent = buttonText;
            }
        } )
    } ).observe( document.body, { childList: true, subtree: true } )
} )

const FeaturedImagesField = ( { thumbnail_id, thumbnails, ...props } ) => {
    thumbnails = [...[thumbnail_id], ...thumbnails]
        .filter( ( value, i, arr ) => arr.indexOf( value ) === i );

    const attachment = useSelect( () => select( 'core' ).getMedia( thumbnail_id ) )
    let buttonContent = thumbnail_id ? <Spinner /> : __( 'Set featured image', 'featured-images' );

    if ( attachment ) {
        let mediaSize = wp.hooks.applyFilters( 'editor.PostFeaturedImage.imageSize', 'large', attachment.id, select('core/editor').getCurrentPostId() )

        if ( !attachment.media_details.sizes[mediaSize] ) {
            mediaSize = 'thumbnail'
        }

        buttonContent = <>
            { createElement( 'img', {
                src: attachment.media_details.sizes[mediaSize]?.source_url || attachment.source_url,
                class: 'editor-post-featured-image__preview-image',
                alt: ''
            } ) }
        </>
    }

    return (
        <MediaUploadCheck>
            <MediaUpload
                modalClass="media-frame-thumbnails"
                onSelect={ media => {
                    media = media.map( ( { id } ) => id )

                    props.setState( {
                        thumbnail_id: media[0] || '',
                        thumbnails: media
                    } )

                    props.updateMeta( media )
                } }
                onClose={ () => {
                    // setTimeout(() => console.log( window.wp.media.frame.off() ), 1000 )
                } }
                multiple="add"
                gallery={ true }
                addToGallery={ !thumbnail_id }
                allowedTypes={ ['image'] }
                value={ thumbnails }
                render={ ( { open } ) => (
                    <div className="editor-post-featured-image__container">
                        <Button className="editor-post-featured-image__preview" onClick={ () => {
                            // some strange error on re-opening
                            // TODO: find some more robust solution
                            try { open() } catch ( e ) { open() }
                        } }>
                            { buttonContent }

                            { thumbnails.length > 1 && <span style={ {
                                position: 'absolute',
                                zIndex: 1,
                                top: 0,
                                right: 0,
                                color: '#000',
                                fontSize: '10px',
                                backgroundColor: 'rgba(255,255,255,.82)',
                                borderBottomLeftRadius: '.25em',
                                padding: '.25em .5em',
                            } }>{ `+ ${thumbnails.length - 1}` }</span> }

                            { attachment && <Flex className="editor-post-featured-image__actions">
                                <Button __next40pxdefaultsize className="editor-post-featured-image__action">{ __( 'Replace' ) }</Button>
                                <Button __next40pxdefaultsize className="editor-post-featured-image__action" onClick={ e => {
                                    e.stopPropagation()

                                    props.setState( {
                                        thumbnail_id: '',
                                        thumbnails: []
                                    } )

                                    props.updateMeta( [] )
                                } }>{ __( 'Remove' ) }</Button>
                            </Flex> }
                        </Button>
                    </div>
                )}
            />
            { thumbnails.length > 1 && <p style={ { fontSize: '12px', color: 'rgb(117, 117, 117)', marginTop: '-.8em' } }>
                { __( 'How and where multiple images are displayed depends on the theme.', 'featured-images' ) }
            </p> }
        </MediaUploadCheck>
    )
}

const FeaturedImagesControl = compose( [
    withState(),
    withSelect( ( select, props ) => ( {
        thumbnail_id: select( 'core/editor' ).getEditedPostAttribute( 'featured_media' ) || '',
        thumbnails: select( 'core/editor' ).getEditedPostAttribute( 'meta' )['_thumbnails'] || [],
    } ) ),
    withDispatch( ( dispatch ) => ( {
        updateMeta( _thumbnails ) {
            dispatch( 'core/editor' ).editPost( {
                featured_media: _thumbnails[0] || null,
                meta: { _thumbnails }
            } );
        }
    } ) )
] )( FeaturedImagesField );

const featuredImages = function( OriginalComponent ) {
    return ( props ) => {
        return [
            // createElement( OriginalComponent, props ), // original featured image box
            <FeaturedImagesControl />
        ];
    }
}

addFilter(
    'editor.PostFeaturedImage',
    'utilities/featured-images',
    featuredImages
);