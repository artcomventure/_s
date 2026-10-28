/**
 * Playwright config: utilities tests (`THEME/utilities/tests/`) and project tests (`THEME/tests/`, optional).
 *
 * Usage (in the theme folder, on the host, Google Chrome installed):
 * `npm test` – against `DEV_URL`
 * `npm run test:stage` / `test:live` – against `STAGE_URL` / `LIVE_URL` (`TEST_ENV=stage`)
 * `BASE_URL=http://localhost npm test` – explicit URL
 * `npm run test:visual` – only tests tagged `@visual` (see `visual.spec.mjs`)
 *
 * URLs from `PROJECT/docker/.env`, test options from `.env.tests`, overridden by `.env.local` and the shell.
 * Tests tagged `@write` (change the DB) only run against `DEV_URL`.
 * HTTP auth (e.g. stage): `STAGE_AUTH=user:password` in `docker/.env.local`.
 */

import { existsSync, readFileSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { defineConfig } from '@playwright/test';

// nearest `docker/.env` above the theme (skips `THEME/docker/` template and empty mountpoints like `public/docker/`)
const findDocker = dir => {
	if ( existsSync( join( dir, 'docker/.env' ) ) ) return join( dir, 'docker' );
	return dirname( dir ) === dir ? null : findDocker( dirname( dir ) );
};
const docker = findDocker( resolve( import.meta.dirname, '../../..' ) );
const theme = resolve( import.meta.dirname, '../..' );
const projectTests = join( theme, 'tests' );

// docker env files incl. `${VAR}` interpolation
const env = {};
for ( const file of [ '.env', '.env.tests', '.env.local' ] ) {
	if ( !docker || !existsSync( join( docker, file ) ) ) continue;
	for ( const line of readFileSync( join( docker, file ), 'utf8' ).split( '\n' ) ) {
		// `KEY="value" # comment` or `KEY=value # comment`
		const match = line.match( /^\s*([\w.-]+)\s*=\s*(?:(['"])(.*?)\2|(.*?))\s*(?:\s#.*)?$/ );
		if ( !match ) continue;
		env[match[1]] = ( match[3] ?? match[4] )
			.replace( /\$\{(\w+)\}|\$(\w+)/g, ( _, a, b ) => process.env[a ?? b] ?? env[a ?? b] ?? '' );
	}
}
// test options (e.g. `TEST_PATHS`) for specs; shell wins
for ( const [ name, value ] of Object.entries( env ) ) process.env[name] ??= value;
const get = name => ( process.env[name] ?? '' ).replace( /\/$/, '' );

const target = process.env.TEST_ENV || 'dev';
const baseURL = get( 'BASE_URL' ) || get( `${target.toUpperCase()}_URL` );
if ( !/^https?:\/\//.test( baseURL ) ) {
	throw new Error( `No valid URL for TEST_ENV=${target} ("${baseURL}") – set it in docker/.env(.local) or BASE_URL.` );
}
const [ username, ...password ] = get( `${target.toUpperCase()}_AUTH` ).split( ':' );

// `@visual` only on demand (`TEST_VISUAL=1`, see `npm run test:visual`)
const visual = !!process.env.TEST_VISUAL;
const exclude = [
	...( baseURL === get( 'DEV_URL' ) ? [] : [ /@write/ ] ),
	...( visual ? [] : [ /@visual/ ] ),
];

export default defineConfig( {
	grep: visual ? /@visual/ : undefined,
	grepInvert: exclude.length ? exclude : undefined,
	// generated, not committed; reference screenshots recreated from `live`
	outputDir: join( import.meta.dirname, 'test-results' ),
	snapshotPathTemplate: join( import.meta.dirname, 'visual-snapshots/{arg}{ext}' ),
	reporter: [ [ 'list' ], [ 'html', { open: 'never', outputFolder: join( import.meta.dirname, 'playwright-report' ) } ] ],
	use: {
		baseURL,
		channel: 'chrome',
		ignoreHTTPSErrors: true,
		httpCredentials: username ? { username, password: password.join( ':' ) } : undefined,
	},
	projects: [
		{ name: 'utilities', testDir: import.meta.dirname },
		...( existsSync( projectTests ) ? [ { name: 'project', testDir: projectTests } ] : [] ),
	],
} );
