(function() {

    // all drop zones
    const $dropZones = document.getElementsByClassName('file-drop' );

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
            // create drop zone
            const $dropZone = document.createElement( 'div' );
            $dropZone.classList.add( 'file-drop' );
            $dropZone.setAttribute( 'tabindex', '0' );
            // instruction
            $dropZone.innerHTML = `<span class="placeholder">${Alter.do(
                'file-drop-zone-text',
                $input.hasAttribute( 'multiple' )
                    ? wp.i18n.__( 'Drag and drop the files here<br />or click to select the files.', 'utilities' )
                    : wp.i18n.__( 'Drag and drop the file here<br />or click to select the file.', 'utilities' ),
                $input
            )}</span>`;

            // files list
            const $files = document.createElement( 'ul' );
            $files.classList.add( 'files' );
            $dropZone.prepend( $files );

            // add dro zone to DOM
            $input.before( $dropZone );

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

                [].forEach.call( $input.files = e.dataTransfer.files, file => {
                    const $item = document.createElement( 'li' );
                    $item.innerHTML = `${file.name} `;
                    $item.setAttribute( 'data-type', file.type );

                    const $remove = document.createElement( 'span' );
                    $remove.innerHTML = '&times;';
                    $remove.setAttribute( 'role', 'button' );
                    $remove.setAttribute( 'tabindex', '0' );
                    $remove.setAttribute( 'aria-label', wp.i18n.__( 'Remove File', 'utilities' ) );

                    $remove.addEventListener( 'click', e => {
                        e.stopPropagation()

                        const dT = new DataTransfer();
                        [].forEach.call( $input.files, item => {
                            if ( file === item ) return;
                            dT.items.add( item )
                        } )

                        $input.files = dT.files;

                        $remove.parentElement.remove();
                    } )

                    $item.appendChild( $remove );
                    $files.appendChild( $item );
                } );

                [].forEach.call( $dropZones, $dropZone => {
                    $dropZone.classList.remove( 'drop-here' );
                } )
            } )
        } )

    } )

})()
