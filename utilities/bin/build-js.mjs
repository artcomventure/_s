/**
 * Transpiles `NAME.js` / `NAME.ts` to `NAME.min.js` (esbuild, no bundling).
 *
 * Usage: `node utilities/bin/build-js.mjs [--watch] [--recursive] <file|dir>...`
 *
 * Build: minified, no comments. `--watch`: unminified with source maps.
 * `*.min.js` and `*.d.ts` are skipped; directories are scanned top level only unless `--recursive`.
 *
 * @since 2.0.0
 */

import { readdirSync, statSync } from 'node:fs';
import { join, resolve } from 'node:path';
import * as esbuild from 'esbuild';

const args = process.argv.slice( 2 );
const flag = name => {
	const i = args.indexOf( `--${name}` );
	return i >= 0 && !!args.splice( i, 1 );
};
const watch = flag( 'watch' );
const recursive = flag( 'recursive' );

if ( !args.length ) {
	console.error( 'Usage: build-js.mjs [--watch] [--recursive] <file|dir>...' );
	process.exit( 1 );
}

const isSource = file => /\.(js|ts)$/.test( file ) && !/\.min\.js$|\.d\.ts$/.test( file );

const entries = args.flatMap( path => statSync( path ).isDirectory()
	? readdirSync( path, { recursive } ).map( file => join( path, file ) ).filter( file => isSource( file ) && !file.split( '/' ).includes( 'node_modules' ) )
	: [ path ] );

// `NAME.js` and `NAME.ts` would write the same `NAME.min.js`
const outputs = new Map();
for ( const file of entries ) {
	const out = resolve( file.replace( /\.(js|ts)$/, '.min' ) );
	if ( outputs.has( out ) ) {
		console.error( `Conflict: ${outputs.get( out )} and ${file} both write ${out}.js` );
		process.exit( 1 );
	}
	outputs.set( out, file );
}

const options = {
	entryPoints: [ ...outputs ].map( ( [ out, file ] ) => ( { in: file, out } ) ),
	// absolute `out` paths (relative ones break outside cwd)
	outdir: '/',
	bundle: false,
	// no `format`: keep script semantics (top-level globals, no renaming)
	target: 'es2017',
	charset: 'utf8',
	minify: !watch,
	legalComments: watch ? 'inline' : 'none',
	sourcemap: watch,
	logLevel: 'info',
};

if ( watch ) await ( await esbuild.context( options ) ).watch();
else await esbuild.build( options );
