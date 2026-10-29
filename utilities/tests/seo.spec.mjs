/**
 * SEO basics: title, description, canonical, `lang`, one `h1`, hreflang, real 404 and indexability per environment
 * (`live` must be indexable, `stage` must not: HTTP auth or `noindex`).
 *
 * `TEST_PATHS="/ /about/ /404-check/"` – pages to test (default `/`).
 */

import { request } from '@playwright/test';
import { test, expect } from './fixtures.mjs';

const paths = ( process.env.TEST_PATHS || '/' ).split( /\s+/ ).filter( Boolean );
const target = process.env.TEST_ENV || 'dev';

const isNoindex = ( header, meta ) => /noindex/i.test( `${header || ''} ${meta || ''}` );

for ( const path of paths ) {
	test( `seo ${path}`, async ( { page, baseURL } ) => {
		const response = await page.goto( path );
		const status = response.status();
		const tags = await page.evaluate( () => ( {
			title: document.title.trim(),
			lang: document.documentElement.lang,
			robots: document.querySelector( 'meta[name="robots"]' )?.content,
			description: document.querySelector( 'meta[name="description"]' )?.content?.trim(),
			canonical: document.querySelector( 'link[rel="canonical"]' )?.href,
			h1: document.querySelectorAll( 'h1' ).length,
			hreflang: [ ...document.querySelectorAll( 'link[rel="alternate"][hreflang]' ) ].map( link => link.href ),
		} ) );
		const noindex = isNoindex( response.headers()['x-robots-tag'], tags.robots );
		const { origin } = new URL( baseURL );

		expect.soft( tags.title, 'title' ).toBeTruthy();
		expect.soft( tags.lang, '<html lang>' ).toBeTruthy();
		expect.soft( tags.h1, 'number of <h1>' ).toBe( 1 );
		for ( const href of tags.hreflang ) expect.soft( href, 'hreflang on own domain' ).toMatch( new RegExp( `^${origin}/` ) );

		if ( status >= 400 ) {
			expect.soft( noindex, 'error page noindex' ).toBe( true );
			return;
		}
		expect.soft( tags.description, 'meta description' ).toBeTruthy();
		expect.soft( tags.canonical, 'canonical on own domain' ).toMatch( new RegExp( `^${origin}/` ) );
		if ( target === 'live' ) expect.soft( noindex, 'live page indexable' ).toBe( false );
	} );
}

test( 'seo 404 status', async ( { page } ) => {
	const response = await page.goto( `/seo-404-check-${Date.now()}/` );
	expect( response.status(), 'unknown URL returns 404 (no soft 404)' ).toBe( 404 );
} );

test( 'seo robots.txt', async ( { page } ) => {
	test.skip( target !== 'live', 'live only' );
	const response = await page.goto( '/robots.txt' );
	expect( await response.text(), 'robots.txt blocks everything' ).not.toMatch( /^Disallow:\s*\/\s*$/m );
} );

test( 'seo stage not indexable', async ( { baseURL } ) => {
	test.skip( target !== 'stage', 'stage only' );
	// without credentials, like a crawler
	const context = await request.newContext( { ignoreHTTPSErrors: true } );
	const response = await context.get( baseURL );
	const protectedByAuth = [ 401, 403 ].includes( response.status() );
	const meta = ( await response.text() ).match( /<meta[^>]+name=["']robots["'][^>]*>/i )?.[0];
	expect( protectedByAuth || isNoindex( response.headers()['x-robots-tag'], meta ), 'stage: HTTP auth or noindex' ).toBe( true );
	await context.dispose();
} );
