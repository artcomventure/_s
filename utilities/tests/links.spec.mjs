/**
 * Internal links: every same-origin link on the tested pages answers with status < 400. Redirects are warnings
 * (link to the final URL instead). External links are not checked (flaky, rate limits).
 *
 * `TEST_PATHS="/ /about/"` – pages whose links are checked (default `/`).
 * `LINKS_EXCLUDE="/wp-admin/ /feed/"` – URL parts to skip (default: `wp-admin`, `wp-login`, `logout`).
 */

import { test, expect } from '@playwright/test';

const paths = ( process.env.TEST_PATHS || '/' ).split( /\s+/ ).filter( Boolean );
const exclude = [ '/wp-admin', '/wp-login', 'logout', ...( process.env.LINKS_EXCLUDE || '' ).split( /\s+/ ).filter( Boolean ) ];

const PARALLEL = 5;

// per worker: links in header/footer are checked once
const checked = new Map();

const check = async ( request, url ) => {
	if ( !checked.has( url ) ) checked.set( url, ( async () => {
		const options = { maxRedirects: 0, failOnStatusCode: false };
		let response = await request.head( url, options );
		if ( [ 405, 501 ].includes( response.status() ) ) response = await request.get( url, options );
		const status = response.status();
		const location = response.headers().location;
		if ( status < 300 || status >= 400 || !location ) return { status };

		// redirect: final status, following up to 5 hops
		const final = await request.get( url, { maxRedirects: 5, failOnStatusCode: false } );
		return { status: final.status(), redirect: new URL( location, url ).href };
	} )() );
	return checked.get( url );
};

for ( const path of paths ) {
	test( `links ${path}`, async ( { page, request, baseURL } ) => {
		const response = await page.goto( path );
		const { origin } = new URL( baseURL );
		// error pages link to themselves (e.g. language switcher)
		const self = response.status() >= 400 ? new URL( path, baseURL ).pathname : null;
		const links = [ ...new Set( await page.$$eval( 'a[href]', anchors => anchors.map( anchor => anchor.href.split( '#' )[0] ) ) ) ]
			.filter( url => url.startsWith( `${origin}/` ) && !exclude.some( part => url.includes( part ) ) )
			.filter( url => !self || !new URL( url ).pathname.endsWith( self ) );

		const errors = [], warnings = [];
		for ( let i = 0; i < links.length; i += PARALLEL ) {
			await Promise.all( links.slice( i, i + PARALLEL ).map( async url => {
				const { status, redirect } = await check( request, url );
				if ( status >= 400 ) errors.push( `${status}: ${url}` );
				else if ( redirect ) warnings.push( `redirect: ${url} → ${redirect}` );
			} ) );
		}

		for ( const warning of warnings ) {
			test.info().annotations.push( { type: 'warning', description: warning } );
			console.warn( `warn  ${warning}` );
		}
		expect( errors, `broken links (${links.length} checked)` ).toEqual( [] );
	} );
}
