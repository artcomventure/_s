/**
 * Compiles SCSS to CSS with Dart Sass and Autoprefixer (targets: `.browserslistrc`).
 *
 * Usage: `node utilities/bin/build-css.mjs [--watch] <file|dir> [<out>]`
 *
 * Build: compressed, no source maps. `--watch`: expanded with source maps, recompiles on changes (incl. partials).
 * Directories are scanned recursively for non-partials (`_*.scss` skipped), except `node_modules` and sub-folders
 * with their own package.json (they build themselves). `<out>`: `.css` file or directory, default next to the source.
 *
 * @since 2.0.0
 */

import { existsSync, mkdirSync, readdirSync, statSync, watch as watchDir, writeFileSync } from 'node:fs';
import { createRequire } from 'node:module';
import { basename, dirname, join, relative, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const args = process.argv.slice( 2 );
const watch = args.includes( '--watch' );
const [ input, output ] = args.filter( arg => arg !== '--watch' );

if ( !input || !existsSync( input ) ) {
	console.error( 'Usage: build-css.mjs [--watch] <file|dir> [<out>]' );
	process.exit( 1 );
}

const require = createRequire( import.meta.url );
const sass = require( 'sass' );
const postcss = require( 'postcss' );
const autoprefixer = require( 'autoprefixer' );

const isDir = statSync( input ).isDirectory();
const isSource = file => file.endsWith( '.scss' ) && !basename( file ).startsWith( '_' );

const scan = dir => readdirSync( dir, { withFileTypes: true } ).flatMap( entry => {
	const path = join( dir, entry.name );
	if ( !entry.isDirectory() ) return isSource( entry.name ) ? [ resolve( path ) ] : [];
	if ( entry.name === 'node_modules' || existsSync( join( path, 'package.json' ) ) ) return [];
	return scan( path );
} );

const target = source => {
	const css = ( isDir ? relative( input, source ) : basename( source ) ).replace( /\.scss$/, '.css' );
	if ( !output ) return join( dirname( source ), basename( css ) );
	return resolve( !isDir && output.endsWith( '.css' ) ? output : join( output, css ) );
};

// source => loaded files (null: last compile failed)
const entries = new Map( ( isDir ? scan( input ) : [ resolve( input ) ] ).map( source => [ source, null ] ) );

const compile = async source => {
	const out = target( source );
	try {
		const result = sass.compile( source, {
			style: watch ? 'expanded' : 'compressed',
			sourceMap: watch,
			sourceMapIncludeSources: watch,
			// `@import` stays until the module system migration (`@use`)
			silenceDeprecations: [ 'import' ],
		} );
		// relative to `from`, postcss rebases them to `to`
		if ( result.sourceMap ) result.sourceMap.sources = result.sourceMap.sources.map( url => url.startsWith( 'file:' ) ? relative( dirname( source ), fileURLToPath( url ) ) : url );
		const { css, map } = await postcss( [ autoprefixer ] ).process( result.css, {
			from: source,
			to: out,
			map: watch && { prev: result.sourceMap, inline: false, annotation: true, sourcesContent: true },
		} );
		mkdirSync( dirname( out ), { recursive: true } );
		writeFileSync( out, `${css}\n` );
		if ( map ) writeFileSync( `${out}.map`, map.toString() );
		entries.set( source, new Set( result.loadedUrls.filter( url => url.protocol === 'file:' ).map( fileURLToPath ) ) );
		if ( watch ) console.log( `Compiled ${relative( '.', source )} to ${relative( '.', out )}.` );
		return true;
	}
	catch ( error ) {
		entries.set( source, null );
		console.error( error.message );
		return false;
	}
};

const results = await Promise.all( [ ...entries.keys() ].map( compile ) );
if ( !watch ) process.exit( results.every( Boolean ) ? 0 : 1 );

// Watch the folders of all loaded files (sass loads partials from `../utilities` etc.)
const watched = new Set();
const pending = new Map();

const observe = () => {
	const folders = [ ...entries ].flatMap( ( [ source, files ] ) => [ dirname( source ), ...[ ...files ?? [] ].map( dirname ) ] );
	if ( isDir ) folders.push( ...scan( input ).map( dirname ), resolve( input ) );
	for ( const folder of folders ) {
		if ( watched.has( folder ) || !existsSync( folder ) ) continue;
		watched.add( folder );
		watchDir( folder, ( event, name ) => name && changed( join( folder, name ) ) );
	}
};

const changed = file => {
	if ( !file.endsWith( '.scss' ) ) return;
	if ( isDir && isSource( file ) && !entries.has( file ) && existsSync( file ) && scan( input ).includes( file ) ) entries.set( file, null );
	for ( const [ source, files ] of entries ) {
		if ( files ? !files.has( file ) : false ) continue;
		// debounce editors' multiple write events
		clearTimeout( pending.get( source ) );
		pending.set( source, setTimeout( () => compile( source ).then( observe ), 50 ) );
	}
};

observe();
console.log( 'Sass is watching for changes. Press Ctrl-C to stop.' );
