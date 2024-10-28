(function( $ ) {

    // ---
    // autocomplete

    let searchRequest

    function attachAutocomplete( $inputs ) {

        $inputs.each( function() {
            const post_type = $(this).closest( '.list-post-type-wrapper' ).data( 'post-type' )

            // UX
            const $input = $(this).on( 'focus click', function () {
                // select all text
                if ( $input.hasClass(  'selected' ) ) $input.select()
            } ).on( 'keydown', function( e ) {
                // once entry is selected don't allow any changes
                if ( $input.hasClass(  'selected' ) ) {
                    // make sure all text is selected
                    $input.select()

                    // ... but removing selection
                    if ( (e.keyCode||e.which) === 8 ) $input.autocomplete( 'enable' ).removeClass( 'selected' )
                        // ... and flush remove post ID input
                        .prev().val( '' )
                    else return false;
                }
            } );

            const Autocomplete = $input.autocomplete({
                source: function( term, suggest ) {
                    try { searchRequest.abort(); } catch( e ) {}

                    searchRequest = fetch( `/wp-json/wp/v2/search?subtype=${post_type}&search=${term.term}` )
                        .then( response => response.json() )
                        .then( posts => {
                            posts.forEach( post => {
                                post.label = post.title;
                            } );

                            suggest( posts )
                        } )
                        .catch( error => {} );
                },
                classes: { 'ui-autocomplete': 'suggest-item-list' },
                position: { my: 'left top-1' },
                select: function( e, ui ) {
                    // mark input as selected and disabled autocomplete
                    $input.addClass( 'selected' ).autocomplete( 'disable' )
                    // set post ID value
                    $input.prev().val( ui.item.id )
                    // set edit link
                    $input.next().prop( 'href', `/wp-admin/post.php?post=${ui.item.id}&action=edit` )

                    window.requestAnimationFrame( function() {
                        $input.select()
                    } )
                }
            } )
            // disable autocomplete for selected inputs
            .autocomplete( $input.val() ? 'disable' : 'enable' );

            // override output (Gutenberg style)
            Autocomplete.autocomplete( 'instance' )._renderItem = function( ul, item ) {
                return $( `<li>` )
                    .append( `<div class="suggest-item">
                        <span class="suggest-item-info">
                            <span class="suggest-item-title">
                                ${item.title
                        .replace( /(<br ?\/?>|\n)/, ' ' )
                        .replace( new RegExp( this.term, 'gi' ), '<mark>$&</mark>' )}
                            </span>
                            <span class="suggest-item-url">${item.url}</span>
                        </span>
                        <span class="suggest-item-type">${item.subtype}</span>
                    </div>` )
                    .appendTo( ul );
            }

            // calculate suggestions width (same as input)
            Autocomplete.autocomplete( 'instance' )._resizeMenu = function() {
                this.menu.element.outerWidth( this.element[0].offsetWidth );
            }

        } );
    }

    // ---
    // event lists widgets UI

    $( '#list-posts-order-widget' ).each( function() {
        const $widget = $(this);
        const $postTypeTabs = $widget.find( '.list-post-type-wrapper' );

        // switch post type
        $widget.find( 'select#switch-list-post-type' )
            .on( 'change', e => {
                $postTypeTabs.each( function() { this.style.display = ''; } );
                $postTypeTabs.filter( `[data-post-type="${e.target.value}"]` ).css( 'display', 'inherit' );
            } ).trigger( 'change' );

        $postTypeTabs.each( function() {
            const $lists = $(this).find( '.posts-list' ).each( function() {
                const post_type = $(this).closest( '.list-post-type-wrapper' ).data( 'post-type' );
                const list = $(this).data( 'list' );

                // add post input
                $(this).find( '.button-secondary' )
                    .on( 'click', function( e ) {
                        e.preventDefault();

                        const $button = $(this);

                        $.get( ajaxurl, {
                            action: 'add-post-to-list',
                            list
                        }, function ( response ) {
                            const $input = $( response )

                            // add event input to list
                            $button.before( $input );

                            attachAutocomplete(
                              $input.find( `input[name="list-posts-order[${post_type}_list][${list}][]"] + input` ),
                            )
                        } );
                    } )

                // sort
                $(this).sortable( {
                    handle: '.dashicons-menu',
                    items: '> div',
                    containment: 'parent'
                } );

                // remove
                $(this).on( 'click', '.dashicons-no-alt', function() {
                    $(this).parent().remove();
                } );

                attachAutocomplete( $(this).find( `input[name="list-posts-order[${post_type}_list][${list}][]"] + input` ) );
            } );

            // switch lists
            $(this).find( `select#switch-${$(this).data( 'post-type' )}-list` )
                .on( 'change', e => {
                    $lists.each( function() { this.style.display = ''; } );
                    $lists.filter( `[data-list="${e.target.value}"]` ).show();
                } ).trigger( 'change' );
        } )
    } );

})( jQuery );