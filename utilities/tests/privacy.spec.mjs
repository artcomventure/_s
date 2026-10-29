/**
 * Privacy test: no external requests and no cookies before consent (fresh browser, nothing accepted).
 *
 * `TEST_PATHS="/ /contact/"` – pages to test (default `/`).
 * `PRIVACY_ALLOW="ccm.jivo.group"` – external requests allowed before consent, URL parts (e.g. the consent tool itself).
 * `PRIVACY_COOKIES="pll_language"` – cookies allowed before consent, names (technically necessary only).
 */

import { test, expect } from './fixtures.mjs';

const paths = ( process.env.TEST_PATHS || '/' ).split( /\s+/ ).filter( Boolean );
const allow = ( process.env.PRIVACY_ALLOW || '' ).split( /\s+/ ).filter( Boolean );
const allowCookies = ( process.env.PRIVACY_COOKIES || '' ).split( /\s+/ ).filter( Boolean );

for ( const path of paths ) {
	test( `privacy ${path}`, async ( { page, context, baseURL } ) => {
		const { host } = new URL( baseURL );
		const external = new Set();
		page.on( 'request', request => {
			const url = new URL( request.url() );
			if ( /^https?:$/.test( url.protocol ) && url.host !== host && !allow.some( part => url.href.includes( part ) ) ) {
				external.add( `${url.origin}${url.pathname}` );
			}
		} );

		await page.goto( path );
		// lazy embeds (maps, videos) load on scroll
		await page.evaluate( async () => {
			for ( let y = 0; y < document.body.scrollHeight; y += innerHeight ) {
				scrollTo( 0, y );
				await new Promise( resolve => setTimeout( resolve, 100 ) );
			}
		} );
		await page.waitForTimeout( 1500 );

		const cookies = ( await context.cookies() )
			.filter( cookie => !allowCookies.includes( cookie.name ) )
			.map( cookie => `${cookie.name} (${cookie.domain})` );

		expect( [ ...external ], 'external requests before consent' ).toEqual( [] );
		expect( cookies, 'cookies before consent' ).toEqual( [] );
	} );
}
