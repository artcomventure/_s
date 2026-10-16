// namespace for utilities' helpers (load order of the files isn't guaranteed)
window.utlts = window.utlts || {};

/**
 * Make sure `global-styles` are removed.
 * See `THEME/inc/gutenberg/gutenberg.php` "remove _unneeded_ CSS"
 */
(function() {
  const $globalStyles = document.getElementById( 'global-styles-inline-css' );
  if ( $globalStyles ) $globalStyles.remove();
})();

/**
 * Set heights as property.
 *
 * @usage: `var(--{ELEMENT_ID}-height)`
 */
(function() {

  const resizeObserver = new ResizeObserver( ( entries ) =>
      entries.forEach( ( entry ) => {
        // element doesn't exist anymore
        if ( !entry.target.isConnected ) return resizeObserver.unobserve( entry.target );

        // set scrollbar width
        if ( entry.target === document.body && $scrollBarTester && $scrollBarTester.isConnected )
          return document.documentElement.style.setProperty(
              '--scrollbar-width',
              `${$scrollBarTester.offsetWidth - $scrollBarTester.clientWidth}px`
          )

        // set height property (`id` is mandatory)
        !!entry.target.id && entry.target.offsetHeight
        && document.documentElement.style.setProperty(
            `--${entry.target.id}-height`,
            Alter.do( 'set-height-property', entry.target.offsetHeight, entry.target ) + 'px'
        )
      } )
  );

  // element for calculating the scroll bar width
  // !!! same js in `gutenberg/src/index.js` !!!
  const $scrollBarTester = document.createElement( 'div' );
  $scrollBarTester.style.cssText = "overflow:scroll; visibility:hidden; position:absolute; pointer-events:none; height:0;"
  $scrollBarTester.innerText = 'This element is for calculating the `--scrollbar-width` property.';
  $scrollBarTester.setAttribute( 'aria-hidden', 'true' )
  document.body.appendChild( $scrollBarTester );
  resizeObserver.observe( document.body )

  Behaviours.add( 'set-height-property', $context => {
    Alter.do( 'set-height-property-for', ['#masthead', '#colophon'] ).forEach( selector => {
      // check if element exists at all
      let $element = document.querySelector( selector );
      if ( !$element ) return;

      if ( $context !== document ) {
        if ( $context !== $element ) {
          // search for element in `$context`
          $element = $context.querySelector( selector );
        }
      }

      // observer element ... if found
      !!$element && resizeObserver.observe( $element );
    } )
  } )

})();

/**
 * Get background color class name (`has-{COLOR NAME}-background-color`) of given `$element`.
 *
 * @param $element
 * @returns {string|undefined}
 */
utlts.getBackgroundColor = $element => {
  let color;

  if ( !!$element && !!$element.classList ) $element.classList.forEach( ( className ) => {
    if ( !color && (/has-(.+)-background-color/.test( className ) || /(.+)-color/.test( className )) )
      color = className;
  } )

  return color;
}

/**
 * Page is shown on mobile device!?
 *
 * Class is set in `UTILITIES/inc/gutenberg/inc/bodyclass/inc/frontend.php`.
 */
(function() {
  const isMobileDevice = document.body.classList.contains( 'mobile-device' );
  utlts.isMobileDevice = () => isMobileDevice;
})();

/**
 * Checks if devive is touch.
 * @returns {boolean}
 *
 * @since 1.20.5
 */
utlts.isTouchDevice = () => {
  return matchMedia( '(pointer: coarse)' ).matches
      && !matchMedia( '(hover: hover)' ).matches;
}

/**
 * Checks if devive is not touch.
 * @returns {boolean}
 *
 * @since 1.20.5
 */
utlts.isNotTouchDevice = () => {
  return matchMedia( '(pointer: fine)' ).matches
      && matchMedia( '(hover: hover)' ).matches;
}

/**
 * Sets `scroll-dir--(down|up)` class to `<html>` according to the scroll direction.
 */
(function() {

  let lastScrollY = 0;
  window.addEventListener('scroll', e => {
    let dir = window.scrollY - lastScrollY;

    if (dir > 0) dir = 'down';
    else if (dir < 0) dir = 'up';
    else dir = '';

    lastScrollY = window.scrollY;

    // change classes on direction change only: each change recalculates the styles of the whole page
    // (compare with the actual classes, pjax replaces them on navigation)
    const $html = document.documentElement;
    const current = ['down', 'up'].find( dir => $html.classList.contains( `scroll-dir--${dir}` ) ) || '';
    if ( dir === current ) return;

    $html.classList.remove( 'scroll-dir--down', 'scroll-dir--up' );
    if ( dir ) $html.classList.add( `scroll-dir--${dir}` );
  } )

})();

/**
 * Create a unique ID.
 *
 * @param {int} length
 * @param {string} prefix
 * @returns {string}
 */
utlts.uuid = ( length, prefix ) => {
  length = Math.abs(parseInt( length ) ) || 8
  if ( !(prefix = (prefix || '').toString().replace( /^[^a-zA-Z]*/, '' )) )
    prefix = 'id'

  const chars = "0123456789BCDFGHJKLMNPQRSTVWXYZbcdfghjklmnpqrstvwxyz0123456789";

  let id = '';
  while ( id.length < length )
    id += chars.charAt( Math.floor(Math.random() * chars.length ) );

  id = `${prefix}-${id}`;

  // make sure ID has not yet been assigned
  if ( document.getElementById( id ) ) return utlts.uuid( length, prefix )
  return id;
}

// deprecated global names (as `window` properties, not `const`: no clash with other scripts) – use `utlts.*`
['getBackgroundColor', 'isMobileDevice', 'isTouchDevice', 'isNotTouchDevice', 'uuid'].forEach( name => window[name] = utlts[name] );
