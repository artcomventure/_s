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
        if ( !entry.target.parentElement ) return resizeObserver.unobserve( entry.target );
        // set height property (`id` is mandatory)
        !!entry.target.id && entry.target.offsetHeight
        && document.documentElement.style.setProperty(
            `--${entry.target.id}-height`,
            Alter.do( 'set-height-property', entry.target.offsetHeight, entry.target ) + 'px'
        )
      } )
  );

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
const getBackgroundColor = $element => {
  let color;

  if ( !!$element && !!$element.classList ) $element.classList.forEach( ( className ) => {
    if ( !color && (/has-(.+)-background-color/.test( className ) || /(.+)-color/.test( className )) )
      color = className;
  } )

  return color;
}

/**
 * Check if given `url` is an internal one.
 *
 * @param url
 * @returns {boolean}
 */
const isInternalUrl = url => {
  const regexp = new RegExp( '^(\/$|\/[^\/]|#|((ht|f)tps?:)?\/\/' + location.host + '|javascript:)' );
  return regexp.test( url )
}

/**
 * Check if given `url` is an external one.
 *
 * @param url
 * @returns {boolean}
 */
const isExternalUrl = url => {
  return !isInternalUrl( url );
}

/**
 * Page is shown on mobile device!?
 *
 * Class is set in `UTILITIES/inc/gutenberg/inc/bodyclass/inc/frontend.php`.
 */
(function() {
  const isMobileDevice = document.body.classList.contains( 'mobile-device' );
  window.isMobileDevice = () => isMobileDevice;
})()