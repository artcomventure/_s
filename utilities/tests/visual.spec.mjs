/**
 * Visual regression test: full-page screenshots compared with reference screenshots (usually from `live`).
 *
 * `npm run test:visual:reference` – create reference from `live` (`:stage` for stage)
 * `npm run test:visual` – compare `dev` with reference
 *
 * `VISUAL_PATHS="/ /about/"` – pages to compare (default `TEST_PATHS`, then `/`).
 * `VISUAL_MASK="video, iframe"` – dynamic elements to cover (default `video, iframe`).
 * `VISUAL_THRESHOLD=0.01` – allowed ratio of different pixels (default `0.01`).
 * `VISUAL_BLOCK="ccm.jivo.group"` – requests to block, URL parts (e.g. consent banners, tracking).
 */

import { join } from 'node:path';
import { test, expect } from '@playwright/test';

const paths = ( process.env.VISUAL_PATHS || process.env.TEST_PATHS || '/' ).split( /\s+/ ).filter( Boolean );
const mask = process.env.VISUAL_MASK || 'video, iframe';
const maxDiffPixelRatio = Number( process.env.VISUAL_THRESHOLD || 0.01 );
const block = ( process.env.VISUAL_BLOCK || '' ).split( /\s+/ ).filter( Boolean );

const VIEWPORTS = {
	mobile: { width: 390, height: 844 },
	desktop: { width: 1440, height: 900 },
};

const slug = path => path.replace( /[^\w]+/g, '-' ).replace( /^-|-$/g, '' ) || 'home';

// lazy content: scroll through the page, load all images, wait for images and fonts
const settle = async page => {
	await page.evaluate( async () => {
		const wait = ( promise, ms ) => Promise.race( [ promise, new Promise( resolve => setTimeout( resolve, ms ) ) ] );
		for ( let y = 0; y < document.body.scrollHeight; y += innerHeight ) {
			scrollTo( 0, y );
			await wait( null, 100 );
		}
		scrollTo( 0, 0 );
		// hidden lazy images (e.g. other slides) never load on their own
		const images = [ ...document.images ];
		images.forEach( image => image.loading = 'eager' );
		await wait( Promise.all( images.map( image => image.decode().catch( () => {} ) ) ), 5000 );
		await wait( document.fonts.ready, 5000 );
		// sliders (`autoHeight`) may measure before fonts are loaded
		for ( let i = 0; i < 50 && document.querySelector( '.swiper:not(.swiper-initialized)' ); i++ ) await wait( null, 100 );
		document.querySelectorAll( '.swiper' ).forEach( slider => slider.swiper?.update() );
		await wait( null, 500 );
	} );
	// no `networkidle`: looping videos never get idle
};

for ( const [ viewport, size ] of Object.entries( VIEWPORTS ) ) {
	for ( const path of paths ) {
		test( `visual ${viewport} ${path} @visual`, async ( { page } ) => {
			await page.setViewportSize( size );
			if ( block.length ) await page.route( url => block.some( part => url.href.includes( part ) ), route => route.abort() );
			await page.goto( path );
			await settle( page );

			await expect( page ).toHaveScreenshot( `${slug( path )}-${viewport}.png`, {
				fullPage: true,
				animations: 'disabled',
				mask: [ page.locator( mask ) ],
				stylePath: join( import.meta.dirname, 'visual.css' ),
				maxDiffPixelRatio,
				timeout: 15000,
			} );
		} );
	}
}
