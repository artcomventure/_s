/**
 * Browser smoke test: loads pages in headless Chrome and reports JS errors, failed requests,
 * PHP errors in the HTML, theme scripts without `defer` and pjax navigation problems.
 *
 * Usage (on the host, Node 22+, Google Chrome installed):
 * `node ~/Develop/wordpress/wp-content/themes/_s/bin/smoke-test.mjs --base http://localhost [--cookies cookies.json] / /blog/ "/?s=test" /404-check/`
 *
 * `--cookies`: JSON array of `{ name, value }` (see `admin-cookies.php`) to test as logged-in user.
 * Exit code 1 if errors were found.
 *
 * @since 1.21.0
 */

import { spawn } from 'node:child_process';
import { mkdtempSync, readFileSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { setTimeout as sleep } from 'node:timers/promises';

const CHROME = process.env.CHROME || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

// args
const args = process.argv.slice( 2 );
const option = name => {
	const i = args.indexOf( `--${name}` );
	return i < 0 ? null : args.splice( i, 2 )[1];
};
const base = ( option( 'base' ) || 'http://localhost' ).replace( /\/$/, '' );
const cookiesFile = option( 'cookies' );
const paths = args.length ? args : [ '/' ];

// chrome with free debugging port (written to `DevToolsActivePort`)
const profile = mkdtempSync( join( tmpdir(), 'smoke-test-' ) );
const chrome = spawn( CHROME, [ '--headless=new', '--disable-gpu', '--no-first-run', '--remote-debugging-port=0', `--user-data-dir=${profile}`, 'about:blank' ], { stdio: 'ignore' } );
const quit = code => { chrome.kill(); sleep( 300 ).then( () => { rmSync( profile, { recursive: true, force: true } ); process.exit( code ); } ); };

let port;
for ( let i = 0; i < 80 && !port; i++ ) {
	await sleep( 100 );
	try { port = readFileSync( join( profile, 'DevToolsActivePort' ), 'utf8' ).split( '\n' )[0]; } catch {}
}
if ( !port ) { console.error( 'Chrome not reachable' ); quit( 2 ); }

const target = ( await ( await fetch( `http://127.0.0.1:${port}/json/list` ) ).json() ).find( t => t.type === 'page' );
const ws = new WebSocket( target.webSocketDebuggerUrl );
await new Promise( r => ws.onopen = r );

// CDP
let id = 0;
const pending = new Map();
const listeners = [];
ws.onmessage = ( { data } ) => {
	const msg = JSON.parse( data );
	if ( msg.id && pending.has( msg.id ) ) { pending.get( msg.id )( msg ); pending.delete( msg.id ); return; }
	listeners.forEach( fn => fn( msg ) );
};
const send = ( method, params = {} ) => new Promise( r => { pending.set( ++id, r ); ws.send( JSON.stringify( { id, method, params } ) ); } );
const evaluate = async expression => ( await send( 'Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true } ) ).result?.result?.value;

// collected per page
let errors = [], warnings = [], requests = new Map(), mainStatus = null;

listeners.push( ( { method, params } ) => {
	if ( method === 'Runtime.exceptionThrown' ) {
		const d = params.exceptionDetails;
		errors.push( `JS exception: ${d.exception?.description?.split( '\n' )[0] ?? d.text} (${d.url ?? 'inline'}:${d.lineNumber + 1})` );
	}
	if ( method === 'Runtime.consoleAPICalled' && params.type === 'error' )
		errors.push( `console.error: ${params.args.map( a => a.value ?? a.description ).join( ' ' )}` );
	if ( method === 'Runtime.consoleAPICalled' && params.type === 'warning' )
		warnings.push( `console.warn: ${params.args.map( a => a.value ?? a.description ).join( ' ' )}` );
	if ( method === 'Network.requestWillBeSent' ) requests.set( params.requestId, { url: params.request.url, type: params.type } );
	if ( method === 'Network.responseReceived' ) {
		const { status, url } = params.response;
		if ( params.type === 'Document' && mainStatus === null ) mainStatus = status;
		else if ( status >= 400 ) report( params.type, `HTTP ${status}: ${url}` );
	}
	if ( method === 'Network.loadingFailed' && !params.canceled ) {
		const request = requests.get( params.requestId );
		if ( request ) report( params.type, `failed (${params.errorText}): ${request.url}` );
	}
} );

// broken scripts/styles break the page, other resources are warnings
const report = ( type, text ) => ( [ 'Script', 'Stylesheet', 'Document' ].includes( type ) ? errors : warnings ).push( text );

const pageChecks = `(() => {
	const html = document.documentElement.outerHTML;
	return {
		php: [ ...html.matchAll( /<b>(Fatal error|Parse error|Warning|Notice|Deprecated)<\\/b>:\\s*([^<]{0,160})/g ) ].map( m => m[1] + ': ' + m[2].trim() ),
		blocking: [ ...document.querySelectorAll( 'script[src*="/wp-content/themes/"]' ) ].filter( s => !s.defer && !s.async && s.type !== 'module' ).map( s => s.id || s.src ),
		pjax: typeof window.pjax !== 'undefined',
	};
})()`;

const waitForLoad = async ( timeout = 15000 ) => {
	const start = Date.now();
	while ( Date.now() - start < timeout ) {
		if ( await evaluate( 'document.readyState' ) === 'complete' ) break;
		await sleep( 200 );
	}
	await sleep( 1500 ); // late errors (behaviours, lazy scripts)
};

await send( 'Runtime.enable' );
await send( 'Network.enable' );
await send( 'Page.enable' );

if ( cookiesFile ) {
	const { hostname } = new URL( base );
	for ( const cookie of JSON.parse( readFileSync( cookiesFile, 'utf8' ) ) )
		await send( 'Network.setCookie', { domain: hostname, path: '/', ...cookie } );
}

let failed = 0;
let pjaxChecked = false;
const blocking = new Set();

for ( const path of paths ) {
	const url = /^https?:/.test( path ) ? path : base + ( path.startsWith( '/' ) ? '' : '/' ) + path;
	errors = []; warnings = []; requests = new Map(); mainStatus = null;

	await send( 'Page.navigate', { url } );
	await waitForLoad();
	const checks = await evaluate( pageChecks ) || { php: [], blocking: [], pjax: false };

	checks.php.forEach( e => errors.push( `PHP ${e}` ) );
	// full list once, then only new ones
	const newBlocking = checks.blocking.filter( s => !blocking.has( s ) );
	newBlocking.forEach( s => blocking.add( s ) );
	if ( newBlocking.length ) warnings.push( `theme scripts without defer: ${newBlocking.join( ', ' )}` );
	else if ( checks.blocking.length ) warnings.push( `${checks.blocking.length} theme scripts without defer (see above)` );
	if ( mainStatus >= 500 ) errors.push( `HTTP ${mainStatus}` );

	// pjax: once, on the first page that has it
	if ( checks.pjax && !pjaxChecked && !cookiesFile ) {
		pjaxChecked = true;
		const link = await evaluate( `(() => {
			const a = [ ...document.querySelectorAll( 'main a[href], nav a[href], header a[href]' ) ].find( a => a.origin === location.origin && !a.hash && a.pathname !== location.pathname && !/wp-(admin|login)|\\.(pdf|jpe?g|png|zip)$/i.test( a.href ) && !a.target );
			if ( !link ) return null;
			window.__smokeTest = true;
			link.click();
			return link.href;
		})()` );
		if ( link ) {
			await sleep( 2500 );
			const state = await evaluate( `({ href: location.href, kept: window.__smokeTest === true })` );
			if ( !state?.kept ) errors.push( `pjax: full reload on ${link}` );
			else if ( state.href.replace( /\/$/, '' ) !== link.replace( /\/$/, '' ) ) errors.push( `pjax: expected ${link}, got ${state.href}` );
			await evaluate( 'history.back()' );
			await sleep( 2000 );
			if ( await evaluate( 'window.__smokeTest === true' ) !== true ) errors.push( 'pjax: full reload on history.back()' );
			console.log( `   pjax: ${url} → ${link} → back` );
		}
	}

	if ( errors.length ) failed++;
	console.log( `${errors.length ? '✗' : '✓'} ${mainStatus ?? '---'} ${url}` );
	[ ...new Set( errors ) ].forEach( e => console.log( `   ERROR ${e}` ) );
	[ ...new Set( warnings ) ].forEach( w => console.log( `   warn  ${w}` ) );
}

console.log( failed ? `\n${failed} of ${paths.length} page(s) with errors` : `\nAll ${paths.length} page(s) without errors` );
ws.close();
quit( failed ? 1 : 0 );
