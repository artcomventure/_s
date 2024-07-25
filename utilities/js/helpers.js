/**
 * Get background color class name (`has-{COLOR NAME}-background-color`) of given `$element`.
 *
 * @param $element
 * @returns {string|undefined}
 */
const getBackgroundColor = ( $element ) => {
  let color;

  if ( !!$element && !!$element.classList ) $element.classList.forEach( ( className ) => {
    if ( !color && (/has-(.+)-background-color/.test( className ) || /(.+)-color/.test( className )) )
      color = className;
  } )

  return color;
}