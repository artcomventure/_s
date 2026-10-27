/**
 * Switches a project theme's build to 2.0: babel → esbuild (`utilities/bin/build-js.mjs`), node-sass → Dart Sass
 * (`utilities/bin/build-css.mjs`), ESLint/TypeScript/Stylelint. Creates missing config files from the boilerplate,
 * rewrites the build scripts in all package.json files, adds lint scripts and syncs devDependencies.
 *
 * Usage (on the host, needs utilities ≥ 2.0 in the project):
 * `node ~/Develop/wordpress/wp-content/themes/_s/bin/migrate-build.mjs <theme> [--write]`
 *
 * Without `--write`: dry run, prints the planned changes only. Formatting (tabs, blank lines) is kept;
 * existing config files are never overwritten. Exit code 2 if commands need manual conversion.
 *
 * @since 2.0.0
 */

import { copyFileSync, existsSync, mkdirSync, readFileSync, readdirSync, writeFileSync } from 'node:fs';
import { dirname, join, posix as path, relative, resolve, sep } from 'node:path';
import { fileURLToPath } from 'node:url';

const args = process.argv.slice( 2 );
const option = name => {
	const i = args.indexOf( `--${name}` );
	return i < 0 ? null : args.splice( i, 2 )[1];
};
const flag = name => {
	const i = args.indexOf( `--${name}` );
	return i >= 0 && !!args.splice( i, 1 );
};
const boilerplate = resolve( option( 'boilerplate' ) || join( dirname( fileURLToPath( import.meta.url ) ), '..' ) );
const write = flag( 'write' );
const theme = args[0] && resolve( args[0] );

if ( !theme ) {
	console.error( 'Usage: migrate-build.mjs <theme> [--boilerplate <dir>] [--write]' );
	process.exit( 1 );
}
if ( !existsSync( join( theme, 'package.json' ) ) ) {
	// suggest theme folders below a project root
	const themes = join( theme, 'wp-content/themes' );
	const found = existsSync( themes ) ? readdirSync( themes ).filter( dir => existsSync( join( themes, dir, 'utilities' ) ) ) : [];
	console.error( `No package.json in ${theme} – run it in the theme folder (the one containing utilities/).` );
	if ( found.length ) console.error( `Found: ${found.map( dir => join( themes, dir ) ).join( ', ' )}` );
	process.exit( 1 );
}

const JS = join( theme, 'utilities/bin/build-js.mjs' );
const CSS = join( theme, 'utilities/bin/build-css.mjs' );
const CONFIGS = [ 'eslint.config.mjs', 'stylelint.config.mjs', 'tsconfig.json', 'js/globals.d.ts', '.npmrc', '.nvmrc', '.browserslistrc', 'utilities/eslint.config.mjs', 'utilities/stylelint.config.mjs' ];
const ADD_DEPS = [ '@eslint/js', 'autoprefixer', 'esbuild', 'eslint', 'globals', 'postcss', 'sass', 'stylelint', 'stylelint-config-recommended-scss', 'typescript', 'typescript-eslint' ];
const BABEL_DEPS = [ '@babel/cli', '@babel/core', '@babel/preset-env', 'babel-preset-minify' ];
const BABEL = /babel (\S+) (-o|-d) (\S+)([^"]*)"/g;
const NODE_SASS = /node-sass((?:\s+(?!&|\||;)[^\s"]+)+)/g;
const NODE_SASS_VALUE = [ '-o', '--output', '--output-style', '--source-map', '--indent-type', '--indent-width', '--include-path', '--precision', '--linefeed' ];
const NODE_SASS_FLAG = [ '-w', '--watch', '-r', '--recursive', '--source-map-embed', '--source-map-contents', '--omit-source-map-url' ];
const uses = ( text, command ) => new RegExp( `"[^"]*\\b${command}\\s` ).test( text );

// config files: create missing ones from the boilerplate, report differing ones
const created = [];
for ( const config of CONFIGS ) {
	const target = join( theme, config ), source = join( boilerplate, config );
	if ( !existsSync( source ) ) continue;
	// browserslist refuses a second config
	if ( config === '.browserslistrc' && 'browserslist' in JSON.parse( readFileSync( join( theme, 'package.json' ), 'utf8' ) ) ) continue;
	if ( !existsSync( target ) ) {
		created.push( config );
		if ( write ) {
			mkdirSync( dirname( target ), { recursive: true } );
			copyFileSync( source, target );
		}
	}
	else if ( readFileSync( target, 'utf8' ) !== readFileSync( source, 'utf8' ) ) console.log( `Kept (differs from boilerplate): ${config}` );
}
if ( created.length ) console.log( `${write ? 'Created' : 'To create'} from boilerplate: ${created.join( ', ' )}` );

const posix = file => file.split( sep ).join( '/' );
const norm = file => path.normalize( file.replace( /^'|'$/g, '' ) ).replace( /(.)\/$/, '$1' );
const isSource = file => /\.(js|ts)$/.test( file ) && !/\.min\.js$|\.d\.ts$/.test( file );

const packages = readdirSync( theme, { recursive: true } )
	.filter( file => file.endsWith( 'package.json' ) && !file.split( sep ).includes( 'node_modules' ) )
	.map( file => join( theme, file ) );

const warnings = [];
const manual = [];
const results = new Map();
const covered = new Set();
const nested = [];

for ( const file of packages ) {
	const dir = dirname( file );
	const isRoot = dir === theme;
	const name = posix( relative( theme, file ) );
	let text = readFileSync( file, 'utf8' );
	const before = text;

	// babel → build-js.mjs
	text = text.replace( BABEL, ( match, src, mode, dst, rest ) => {
		const source = norm( src );
		const consistent = mode === '-o'
			? norm( dst ) === norm( src.replace( /\.js$/, '.min.js' ) )
			: norm( dst ) === ( /\.(js|ts)$/.test( source ) ? path.dirname( source ) : source );
		if ( !consistent || /[&;|]/.test( rest ) ) {
			manual.push( `${name}: ${match.slice( 0, -1 )}` );
			return match;
		}

		const rel = posix( relative( dir, JS ) );
		const watch = rest.includes( '--watch' );
		const isDir = !/\.(js|ts)$/.test( source );
		const files = isDir ? readdirSync( join( dir, source ), { recursive: true } ).filter( f => isSource( f ) && !f.split( sep ).includes( 'node_modules' ) ) : [ '' ];

		for ( const f of files ) {
			const abs = join( dir, source, f );
			if ( isRoot || !f.includes( sep ) ) covered.add( abs );
			// babel recursed into directories (unless ignored via `*/**`); the new script only does with `--recursive` (theme root)
			else if ( !rest.includes( '*/**' ) && existsSync( abs.replace( /\.(js|ts)$/, '.min.js' ) ) ) nested.push( { name, base: join( dir, source ), abs } );
		}

		return isRoot
			? `node ${rel}${watch ? ' --watch' : ''}${isDir ? ' --recursive' : ''} ${source}"`
			: `node ${rel} ${source}${watch ? ' --watch' : ''}"`;
	} );

	// node-sass → build-css.mjs (output style follows `--watch`: expanded + source maps, else compressed)
	text = text.replace( NODE_SASS, ( match, rest ) => {
		const tokens = rest.trim().split( /\s+/ );
		let input = null, output = null, watch = false, unknown = null;
		for ( let i = 0; i < tokens.length; i++ ) {
			const t = tokens[i];
			if ( t === '>' ) output = tokens[++i];
			else if ( t === '-o' || t === '--output' ) output = tokens[++i];
			else if ( NODE_SASS_VALUE.includes( t ) ) i++;
			else if ( t === '-w' || t === '--watch' ) watch = true;
			else if ( NODE_SASS_FLAG.includes( t ) ) continue;
			else if ( t.startsWith( '-' ) || input ) unknown = t;
			else input = t;
		}
		if ( unknown || !input || !output ) {
			manual.push( `${name}: ${match}` );
			return match;
		}

		const source = norm( input );
		const isFile = source.endsWith( '.scss' );
		const target = norm( output );
		const implicit = isFile
			? target === path.dirname( source ) || target === source.replace( /\.scss$/, '.css' )
			: target === source;

		return `node ${posix( relative( dir, CSS ) )} ${source}${implicit ? '' : ` ${target}`}${watch ? ' --watch' : ''}`;
	} );

	for ( const line of text.split( '\n' ) ) {
		if ( ( uses( line, 'babel' ) || uses( line, 'node-sass' ) ) && !manual.some( m => m.startsWith( name ) ) ) manual.push( `${name}: ${line.trim()}` );
	}

	results.set( file, { name, before, text } );
}

// nested sources babel built that nothing builds anymore (sub-folders with own package.json build themselves)
const lost = new Map();
for ( const { name, base, abs } of nested ) {
	if ( covered.has( abs ) ) continue;
	let sub = dirname( abs ), own = false;
	while ( sub !== base && !own ) {
		own = existsSync( join( sub, 'package.json' ) );
		sub = dirname( sub );
	}
	if ( !own ) lost.set( abs, name );
}
for ( const [ abs, name ] of lost ) warnings.push( `${name}: ${posix( relative( theme, abs ) )} was built by babel (recursive), not by build-js.mjs anymore` );

// script line helpers, keeping indentation
const SCRIPT_LINE = key => new RegExp( `^([ \\t]*)"${key.replace( /[:*]/g, '\\$&' )}": "(?:[^"\\\\]|\\\\.)*",?\\n`, 'm' );
const insertScript = ( text, anchor, lines, after ) => {
	const match = text.match( SCRIPT_LINE( anchor ) );
	if ( !match ) return null;
	const block = lines.map( line => line ? `${match[1]}${line}\n` : '\n' ).join( '' );
	return text.replace( match[0], after ? match[0] + block : block + match[0] );
};

// lint scripts: add missing ones (first one after `build:js` with a blank line), `prebuild` before `build`
const lint = ( file, scripts ) => {
	const result = results.get( file );
	if ( !result ) return;
	let text = result.text;
	let anchor = null;
	for ( const [ key, value ] of scripts ) {
		const json = JSON.parse( text ).scripts || {};
		const line = `"${key}": ${JSON.stringify( value )},`;
		if ( json[key] === value ) anchor = key;
		else if ( json[key] === undefined ) {
			const added = anchor ? insertScript( text, anchor, [ line ], true ) : insertScript( text, 'build:js', [ '', line ], true );
			if ( added ) {
				text = added;
				anchor = key;
			}
			else warnings.push( `${result.name}: no \`build:js\` script – add lint scripts by hand` );
		}
		// single-tool `lint` of an earlier migration → run all `lint:*`
		else if ( key === 'lint' && json.lint === 'eslint .' ) {
			text = text.replace( SCRIPT_LINE( 'lint' ), ( m, indent ) => `${indent}${line}\n` );
			anchor = key;
		}
		else {
			warnings.push( `${result.name}: script \`${key}\` differs from the boilerplate – check by hand` );
			anchor = key;
		}
	}
	if ( !( JSON.parse( text ).scripts || {} ).prebuild ) text = insertScript( text, 'build', [ '"prebuild": "npm run lint",' ], false ) || text;
	result.text = text;
};

lint( join( theme, 'package.json' ), [
	[ 'lint', 'npm-run-all --parallel lint:*' ],
	[ 'lint:js', 'eslint js' ],
	[ 'lint:ts', 'tsc --noEmit' ],
	[ 'lint:css', 'stylelint "css/**/*.scss"' ],
] );
if ( existsSync( join( theme, 'utilities/eslint.config.mjs' ) ) || created.includes( 'utilities/eslint.config.mjs' ) ) lint( join( theme, 'utilities/package.json' ), [
	[ 'lint', 'npm-run-all --parallel lint:*' ],
	[ 'lint:js', 'eslint .' ],
	[ 'lint:css', 'stylelint "**/*.scss"' ],
] );

// devDependencies (root): add from boilerplate, drop replaced tools once no package.json uses them
{
	const root = results.get( join( theme, 'package.json' ) );
	const bpDeps = JSON.parse( readFileSync( join( boilerplate, 'package.json' ), 'utf8' ) ).devDependencies || {};
	const all = [ ...results.values() ].map( r => r.text ).join( '\n' );
	const postcssConfig = readdirSync( theme ).some( f => /^(postcss\.config\.|\.postcssrc)/.test( f ) );
	const remove = [
		...( uses( all, 'babel' ) ? [] : BABEL_DEPS ),
		...( uses( all, 'node-sass' ) ? [] : [ 'node-sass' ] ),
		...( uses( all, 'postcss' ) || postcssConfig ? [] : [ 'postcss-cli' ] ),
	];
	const match = root.text.match( /^([ \t]*)"devDependencies": \{\n([\s\S]*?)\n\1\}/m );
	if ( match ) {
		const indent = match[2].match( /^[ \t]*/ )[0];
		const deps = JSON.parse( `{${match[2]}}` );
		for ( const dep of ADD_DEPS ) if ( !deps[dep] && bpDeps[dep] ) deps[dep] = bpDeps[dep];
		for ( const dep of remove ) delete deps[dep];
		const body = Object.keys( deps ).sort().map( dep => `${indent}"${dep}": "${deps[dep]}"` ).join( ',\n' );
		const engines = /^\s*"engines":/m.test( root.text ) ? '' : `${match[1]}"engines": {\n${indent}"node": ">=24"\n${match[1]}},\n`;
		root.text = root.text.replace( match[0], `${engines}${match[1]}"devDependencies": {\n${body}\n${match[1]}}` );
		if ( uses( all, 'babel' ) || uses( all, 'node-sass' ) ) warnings.push( 'babel/node-sass devDependencies kept: still used (see manual)' );
	}
	else warnings.push( 'package.json: no devDependencies block – add devDependencies by hand' );
}

// report
let changed = 0;
for ( const [ file, { name, before, text } ] of results ) {
	if ( before === text ) continue;
	changed++;
	console.log( `\n== ${name}` );
	const old = before.split( '\n' ), now = text.split( '\n' );
	for ( const line of old ) if ( !now.includes( line ) ) console.log( `- ${line.trim()}` );
	for ( const line of now ) if ( !old.includes( line ) ) console.log( `+ ${line.trim()}` );
	if ( write ) writeFileSync( file, text );
}

for ( const script of [ JS, CSS ] ) if ( !existsSync( script ) ) warnings.unshift( `${posix( relative( theme, script ) )} is missing – sync utilities/ from the boilerplate first` );

console.log( `\n${changed} package.json ${write ? 'written' : 'to change (dry run, use --write)'}` );
if ( warnings.length ) console.log( `\nWarnings:\n${warnings.map( w => `- ${w}` ).join( '\n' )}` );
if ( manual.length ) console.log( `\nManual (not converted):\n${manual.map( m => `- ${m}` ).join( '\n' )}` );
process.exit( manual.length ? 2 : 0 );
