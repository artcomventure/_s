import { DotLottie, setWasmUrl } from '@lottiefiles/dotlottie-wc';

// load the WASM file locally instead of from the library's CDN (URL set in `dotlottie-wc.php`)
if ( window.dotlottieWasmUrl ) setWasmUrl( window.dotlottieWasmUrl );

(function() {

  const io = new IntersectionObserver( entries => {
    entries.forEach( entry => {
      // check if element is connected to the `document`
      if ( !entry.target.isConnected ) return io.unobserve( entry.target );
      if ( !entry.isIntersecting || !entry.target.hasAttribute( 'data-src' ) ) return;

      // make sure dotlottie is ready before setting the src attribute
      const src = entry.target.getAttribute( 'data-src' );
      entry.target.removeAttribute( 'data-src' );
      // lazy loading done
      io.unobserve( entry.target );

      // constantly checking status
      (function setSrc() {
        if ( !entry.target.isConnected ) return;

        if ( entry.target.dotLottie?.isReady )
          entry.target.setAttribute( 'src', src );
        else requestAnimationFrame( setSrc )
      })()
    } )
  }, { threshold: [0,1] } )

  Behaviours.add( 'dotlottie', $context => {

    $context.querySelectorAll( 'dotlottie-wc' ).forEach( $dotlottie  => {
      // 'load' event is not triggered :/ this is the workaround
      // set right animations dimensions to canvas
      (function setDimensions() {
        if ( !$dotlottie.isConnected ) return;

        if ( $dotlottie.dotLottie?.isLoaded ) {
          const size = $dotlottie.dotLottie.animationSize()
          $dotlottie.dotLottie.canvas.width = size.width
          $dotlottie.dotLottie.canvas.height = size.height
          $dotlottie.dotLottie.resize()
        }
        else requestAnimationFrame( setDimensions )
      })()

      if ( $dotlottie.hasAttribute( 'data-src' ) )
        io.observe( $dotlottie )

      if ( !$dotlottie.hasAttribute( 'autoplay' ) ) {
        const event = $dotlottie.getAttribute( 'data-start' );
        if ( event !== 'custom' ) {
          $dotlottie.addEventListener( event, e => {
            $dotlottie.dotLottie.play()
          } )
        }
      }
    } )

  } );

})()
