// namespace for utilities' helpers (load order of the files isn't guaranteed)
window.utlts = window.utlts || {};

/**
 * Set classes `in-viewport` or `outside-viewport`.
 */
utlts.inViewportObserver = new IntersectionObserver( entries => {
  entries.forEach( entry => {
    // check if element is still connected to the `document`
    if ( !entry.target.isConnected ) return utlts.inViewportObserver.unobserve( entry.target );

    entry.target.classList.add( entry.isIntersecting ? 'in-viewport' : 'outside-viewport' );
    entry.target.classList.remove( entry.isIntersecting ? 'outside-viewport' : 'in-viewport' );

    entry.target.dispatchEvent( new CustomEvent( 'in-viewport:change', {
      bubbles: true,
      detail: {
        isInViewport: entry.isIntersecting
      }
    } ) )
  } )
}, { threshold: [0, .25, .33333, .5, .66666, .75, 1] } )

/**
 * Check if element is in viewport or not.
 *
 * @param $element
 * @returns {boolean}
 */
utlts.isInViewport = $element => {
  // make use of `IntersectionObserver`
  if ( $element.classList.contains( 'in-viewport' ) ) return true;
  if ( $element.classList.contains( 'outside-viewport' ) ) return false;

  // calculate the _old way_
  const rect = $element.getBoundingClientRect();
  return rect.bottom > 0 && rect.right > 0
    && rect.left < window.innerWidth
    && rect.top < window.innerHeight;
}

utlts.isOutsideViewport = $element => !utlts.isInViewport( $element );
utlts.isNotInViewport = $element => utlts.isOutsideViewport( $element );

// deprecated global names (as `window` properties, not `const`: no clash with other scripts) – use `utlts.*`
['inViewportObserver', 'isInViewport', 'isOutsideViewport', 'isNotInViewport'].forEach( name => window[name] = utlts[name] );
