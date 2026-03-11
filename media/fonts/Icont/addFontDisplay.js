/**
 * Adds `font-display: swap;` to Icont font.
 * @since 1.20.2
 */

const fs = require( 'fs' );
const path = require( 'path' );

// path of the file in which the string should be replaced
const filePath = path.join( __dirname, 'font.css' );

try {
  let fileContent = fs.readFileSync( filePath, 'utf8' ).trim(); // read
  fileContent = `@font-face{font-display:swap;${fileContent.slice(11)}`; // replace
  fs.writeFileSync( filePath, fileContent, 'utf8' ); // write

  console.log( `String replacement in ${path.basename(filePath)} was successfully.` );
} catch ( error ) {
  console.error( `Error patching the file: ${error.message}` );
}