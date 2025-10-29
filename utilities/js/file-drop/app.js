(function() {

    // all drop zones
    const $dropZones = document.getElementsByClassName('file-drop--zone' );

    // _highlight_ drop zones
    window.addEventListener('dragover', e => {
        [].forEach.call( $dropZones, $dropZone => {
            $dropZone.classList.add( 'drop-here' );
        } )
    } )

    // _unhighlight_ drop zones
    window.addEventListener('dragleave', e => {
        [].forEach.call( $dropZones, $dropZone => {
            $dropZone.classList.remove( 'drop-here' );
        } )
    } )

    Behaviours.add( 'file-drop', $context => {

        $context.querySelectorAll( 'input[type=file]' ).forEach( $input => {
            $input.addEventListener( 'change', e => {
                $files.innerHTML = ''; // clear

                [].forEach.call( $input.files, file => {
                    const $item = document.createElement( 'li' );
                    $item.innerHTML = `${file.name} `;
                    $item.setAttribute( 'data-type', file.type );

                    const $remove = document.createElement( 'span' );
                    $remove.innerHTML = '&times;';
                    $remove.setAttribute( 'role', 'button' );
                    $remove.setAttribute( 'tabindex', '0' );
                    $remove.setAttribute( 'aria-label', wp.i18n.__( 'Remove File', 'file-drop' ) );

                    ['click', 'keydown'].forEach( event => {
                        $remove.addEventListener( event, e => {
                            if ( e.type === 'keydown' && e.key !== 'Enter' ) return;

                            e.stopPropagation();

                            const dT = new DataTransfer();
                            [].forEach.call( $input.files, item => {
                                if ( file === item ) return;
                                dT.items.add( item )
                            } )

                            $input.files = dT.files;

                            $remove.parentElement.remove();
                        } )
                    } )

                    $item.appendChild( $remove );
                    $files.appendChild( $item );
                } );
            } )

            // UI
            const $fileDrop = document.createElement( 'div' );
            $fileDrop.classList.add( 'file-drop' );

            // drop zone
            const $dropZone = document.createElement( 'div' );
            $dropZone.classList.add( 'file-drop--zone' );
            $dropZone.setAttribute( 'role', 'button' );
            $dropZone.setAttribute( 'tabindex', 0 );
            $fileDrop.prepend( $dropZone );
            $dropZone.innerHTML = '<span class="file-drop--text">' + Alter.do(
                'file-drop-zone-text',
                $input.hasAttribute( 'multiple' )
                    ? wp.i18n.__( 'Drag and drop the files here<br />or click to select the files.', 'file-drop' )
                    : wp.i18n.__( 'Drag and drop the file here<br />or click to select the file.', 'file-drop' ),
                $input
            ) + '</span>';

            // files list
            const $files = document.createElement( 'ul' );
            $files.classList.add( 'file-drop--files' );
            $fileDrop.appendChild( $files );

            // add drop zone to DOM
            $input.before( $fileDrop );

            // UI/UX

            // open browser's native file selection window
            ['click', 'keydown'].forEach( event => $dropZone.addEventListener( event, e => {
                if ( e.type === 'keydown' && e.key !== 'Enter' ) return;
                // `new Event` event doesn't work
                $input.dispatchEvent( new MouseEvent( 'click' ) );
            } ) )

            $dropZone.addEventListener( 'dragover', e => {
                e.preventDefault()
                $dropZone.classList.add( 'is-dragover' );
            } )

            $dropZone.addEventListener( 'dragleave', e => {
                $dropZone.classList.remove( 'is-dragover' );
            } )

            $dropZone.addEventListener( 'drop', e => {
                e.preventDefault()

                $dropZone.classList.remove( 'is-dragover' );

                let files = e.dataTransfer.files;
                if ( files.length ) {
                    const dT = new DataTransfer();

                    $input.files = files;
                    $input.dispatchEvent( new Event( 'change', { bubbles: true } ) );

                    [].forEach.call( files, file => {
                        // validate file type
                        if ( $input.hasAttribute( 'accept' ) ) {
                            const allowedFileTypes = $input.getAttribute( 'accept' ).split(/\s*,\s*/);

                            allowedFileTypes.forEach( type => {
                                if ( new RegExp( `${type}$` ).test( file.name ) || new RegExp( type ).test( file.type ) ) {
                                    dT.items.add( file );
                                }
                            } );
                        }
                    } )

                    if ( (files = dT.files).length ) {
                        if ( !$input.hasAttribute( 'multiple' ) ) {
                            while ( dT.items[1] ) dT.items.remove(1 )
                            files = dT.files;
                        }
                    }
                }

                $input.files = files;
                $input.dispatchEvent( new Event( 'change' ) );

                [].forEach.call( $dropZones, $dropZone => {
                    $dropZone.classList.remove( 'drop-here' );
                } )
            } )
        } )

    } )

})()
