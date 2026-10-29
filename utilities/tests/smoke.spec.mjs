/**
 * Browser smoke test: JS errors, failed requests, PHP errors in the HTML, theme scripts without `defer`
 * and pjax navigation problems. Playwright port of `THEME/bin/smoke-test.mjs`.
 *
 * `TEST_PATHS="/ /blog/ /?s=test /404-check/"` – pages to test (default `/`).
 * `SMOKE_COOKIES=cookies.json` – test as logged-in user (see `THEME/bin/admin-cookies.php`).
 */

import { readFileSync } from 'node:fs';
import { test, expect } from './fixtures.mjs';

const paths = ( process.env.TEST_PATHS || '/' ).split( /\s+/ ).filter( Boolean );
const cookiesFile = process.env.SMOKE_COOKIES;

const PHP_ERROR = /<b>(Fatal error|Parse error|Warning|Notice|Deprecated)<\/b>:\s*([^<]{0,160})/g;

// broken scripts/styles break the page, other resources are warnings
const CRITICAL = [ 'document', 'script', 'stylesheet' ];

const unique = list => [ ...new Set( list ) ];

const watch = page => {
	const errors = [], warnings = [];
	const report = ( request, text ) => ( CRITICAL.includes( request.resourceType() ) ? errors : warnings ).push( text );
	const isMain = request => request.isNavigationRequest() && request.frame() === page.mainFrame();

	page.on( 'pageerror', error => errors.push( `JS exception: ${error.message}` ) );
	page.on( 'console', message => {
		// failed resources are reported by `response`
		if ( message.text().startsWith( 'Failed to load resource' ) ) return;
		if ( message.type() === 'error' ) errors.push( `console.error: ${message.text()}` );
		if ( message.type() === 'warning' ) warnings.push( `console.warn: ${message.text()}` );
	} );
	page.on( 'response', response => {
		if ( response.status() >= 400 && !isMain( response.request() ) ) report( response.request(), `HTTP ${response.status()}: ${response.url()}` );
	} );
	page.on( 'requestfailed', request => {
		const error = request.failure()?.errorText;
		if ( error !== 'net::ERR_ABORTED' ) report( request, `failed (${error}): ${request.url()}` );
	} );

	return { errors, warnings };
};

test.beforeEach( async ( { context, baseURL } ) => {
	if ( !cookiesFile ) return;
	const { hostname } = new URL( baseURL );
	const cookies = JSON.parse( readFileSync( cookiesFile, 'utf8' ) );
	await context.addCookies( cookies.map( cookie => ( { domain: hostname, path: '/', ...cookie } ) ) );
} );

for ( const path of paths ) {
	test( `page ${path}`, async ( { page } ) => {
		const { errors, warnings } = watch( page );

		const response = await page.goto( path );
		await page.waitForTimeout( 1500 ); // late errors (behaviours, lazy scripts)

		if ( response?.status() >= 500 ) errors.push( `HTTP ${response.status()}` );
		for ( const match of ( await page.content() ).matchAll( PHP_ERROR ) ) errors.push( `PHP ${match[1]}: ${match[2].trim()}` );

		const blocking = await page.$$eval( 'script[src*="/wp-content/themes/"]', scripts => scripts
			.filter( script => !script.defer && !script.async && script.type !== 'module' )
			.map( script => script.id || script.src ) );
		if ( blocking.length ) warnings.push( `theme scripts without defer: ${blocking.join( ', ' )}` );

		for ( const warning of unique( warnings ) ) {
			test.info().annotations.push( { type: 'warning', description: warning } );
			console.warn( `warn  ${warning}` );
		}
		expect( unique( errors ) ).toEqual( [] );
	} );
}

test( 'pjax navigation', async ( { page } ) => {
	test.skip( !!cookiesFile, 'logged in' );

	// first page that has pjax
	let hasPjax = false;
	for ( const path of paths ) {
		await page.goto( path );
		if ( ( hasPjax = await page.evaluate( () => typeof window.pjax !== 'undefined' ) ) ) break;
	}
	test.skip( !hasPjax, 'no page with pjax' );

	const from = page.url();
	const link = await page.evaluate( () => {
		const a = [ ...document.querySelectorAll( 'main a[href], nav a[href], header a[href]' ) ].find( a => a.origin === location.origin && !a.hash && a.pathname !== location.pathname && !/wp-(admin|login)|\.(pdf|jpe?g|png|zip)$/i.test( a.href ) && !a.target );
		if ( !a ) return null;
		window.__smokeTest = true;
		a.click();
		return a.href;
	} );
	test.skip( !link, 'no internal link' );

	await page.waitForTimeout( 2500 );
	expect( await page.evaluate( () => window.__smokeTest ), `full reload on ${link}` ).toBe( true );
	expect( page.url().replace( /\/$/, '' ) ).toBe( link.replace( /\/$/, '' ) );

	await page.evaluate( () => history.back() );
	await page.waitForTimeout( 2000 );
	expect( await page.evaluate( () => window.__smokeTest ), 'full reload on history.back()' ).toBe( true );
	expect( page.url() ).toBe( from );
} );
