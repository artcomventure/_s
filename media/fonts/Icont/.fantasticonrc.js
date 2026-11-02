/**
 * fantasticon config (`npm run icont:generate` in the theme).
 * fantasticon >= 3 doesn't sort icons (glob 9+): assign codepoints alphabetically,
 * in sync with `$Icont` in `THEME/css/_variables.scss`.
 */

const fs = require( 'fs' );
const path = require( 'path' );

const codepoints = {};
const inputDir = path.join( __dirname, 'icons' );
fs.readdirSync( inputDir )
  .filter( file => file.endsWith( '.svg' ) )
  .sort() // by file name like fantasticon 2 (`arrow-left-alt.svg` before `arrow-left.svg`)
  .map( file => file.slice( 0, -4 ) )
  .forEach( ( icon, i ) => codepoints[icon] = 0xf101 + i );

module.exports = {
  inputDir,
  outputDir: __dirname,
  name: 'Icont',
  prefix: 'icont',
  normalize: true,
  assetTypes: ['scss'],
  codepoints,
  // `font-display: swap`, empty alternative text
  templates: { scss: path.join( __dirname, 'Icont.hbs' ) },
};
