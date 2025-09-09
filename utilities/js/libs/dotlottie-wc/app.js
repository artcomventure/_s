import { DotLottie } from '@lottiefiles/dotlottie-wc';

(function() {

  const io = new IntersectionObserver( entries => {
    entries.forEach( entry => {
      if ( entry.target.parentElement ) {
        if ( !entry.isIntersecting ) return;

        if ( entry.target.hasAttribute( 'data-src' ) ) {
          // make sure dotlottie is ready before setting the src attribute
          const src = entry.target.getAttribute( 'data-src' );
          entry.target.removeAttribute( 'data-src' );
          // constantly checking status
          (function setSrc() {
            if ( !entry.target.parentElement ) return;

            if ( entry.target.dotLottie?.isReady )
              entry.target.setAttribute( 'src', src );
            else requestAnimationFrame( setSrc )
          })()
        }
      }

      io.observe( entry.target )
    } )
  }, { threshold: [0,1] } )

  Behaviours.add( 'dotlottie', $context => {

    $context.querySelectorAll( 'dotlottie-wc' ).forEach( $dotlottie  => {
      // 'load' event is not triggered :/ this is the workaround
      // set right animations dimensions to canvas
      (function setDimensions() {
        if ( !$dotlottie.parentElement ) return;

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
