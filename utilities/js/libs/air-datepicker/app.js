import AirDatepicker from './module/air-datepicker';
import localeDe from './module/locale/de';
import localeEn from './module/locale/en';

const locale = (document.documentElement.getAttribute( 'lang' ) || 'en')
  .split( '-' )[0];

Behaviours.add( 'datepicker', $context => {

  [].forEach.call( $context.querySelectorAll( 'input[type="date"]' ), $input => {

    // remove native datepicker
    $input.setAttribute( 'type', 'text' );

    let options = {
      selectedDates: [Date.parse( $input.value )],
      dateFormat: phpDateFormatToJs( $input.getAttribute( 'data-format' ) || '' ),
      minDate: $input.getAttribute( 'min' ) || '',
      maxDate: $input.getAttribute( 'max' ) || '',
      locale: { de: localeDe, en: localeEn }[locale],
      position: 'bottom center',
      buttons: ['today'],
      onSelect( { date, formattedDate, datepicker } ) {
        $input.dispatchEvent( new CustomEvent( 'airDatepicker:select', {
          detail: date,
          bubbles: true
        } ) )
      }
    }

    options = Alter.do( 'airDatepicker:options', options );

    const dp = new AirDatepicker( $input, options );

    $input.addEventListener( 'onSelect', e => {
      console.log( e );
    } )

    $input.dispatchEvent( new CustomEvent( 'datepicker:init', {
      detail: { dp, options },
      bubbles: true
    } ) )
  } )

} );

// ---
// Helpers.

/**
 * Convert PHP datetime format to air-datepicker's format.
 *
 * @param format
 * @returns string
 */
function phpDateFormatToJs( format ) {

  // https://www.php.net/manual/en/datetime.format.php
  // https://air-datepicker.com/docs#dateFormat
  // https://air-datepicker.com/docs#timeFormat

  let replacements = {
    'j': 'd', // day of the month without leading zeros
    'd': 'dd', // day of the month, 2 digits with leading zeros
    'D': 'E', // textual representation of a day, three letters
    'l': 'EEEE', // full textual representation of the day of the week

    'n': 'M', // numeric representation of a month, without leading zeros
    'm': 'MM', // numeric representation of a month, with leading zeros
    'M': 'MMM', // short textual representation of a month, three letters
    'F': 'MMMM', // full textual representation of a month

    'y': 'yy', // two digit representation of a year
    'Y': 'yyyy', // full numeric representation of a year, at least 4 digits
    'o': 'yyyy', // ISO 8601 week-numbering year

    'g': 'h', // 12-hour format of an hour without leading zeros
    'G': 'H', // 24-hour format of an hour without leading zeros
    'h': 'hh', // 12-hour format of an hour with leading zeros
    'H': 'HH', // 24-hour format of an hour with leading zeros

    'i': 'mm', // minutes with leading zeros

    'A': 'AA', // uppercase Ante meridiem and Post meridiem
    'a': 'AA', // no lowercase format; we use uppercase as fallback
  };

  return format.replace( /[a-z]+/ig, match => match in replacements ? replacements[match] : match );
}