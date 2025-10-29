const fs = require( 'fs' );
const path = require( 'path' );

// path of the file in which the string should be replaced
const filePath = path.join( __dirname, 'build', 'app.js' );

// strings to replace
const needles = [
  'https://unpkg.com/${Xt}@${Jt}/dist/dotlottie-player.wasm',
  'https://cdn.jsdelivr.net/npm/${Xt}@${Jt}/dist/dotlottie-player.wasm'
];

try {
  // read
  let fileContent = fs.readFileSync( filePath, 'utf8' );

  // search and replace
  needles.forEach( needle => fileContent = fileContent
    .replace( needle, '/wp-content/themes/office/utilities/js/libs/dotlottie-wc/dotlottie-player.wasm' ) )

  // write
  fs.writeFileSync( filePath, fileContent, 'utf8' );

  console.log( `String replacement in ${path.basename(filePath)} was successfully.` );
} catch ( error ) {
  console.error( `Error patching the file: ${error.message}` );
}