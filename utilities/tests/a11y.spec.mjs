/**
 * Accessibility test (axe-core, WCAG 2.2 A/AA): violations with impact in `A11Y_IMPACT` fail, others are warnings;
 * checks axe can't decide (e.g. contrast on images) are listed for manual review.
 *
 * `TEST_PATHS="/ /about/"` – pages to test (default `/`).
 * `A11Y_IMPACT="critical serious"` – impacts that fail the test (default `critical serious`; also `moderate minor`).
 * `A11Y_EXCLUDE=".ccm-root"` – elements to skip, selectors (e.g. third-party widgets); dev info panel always skipped.
 */

import { test, expect } from '@playwright/test';
import { AxeBuilder } from '@axe-core/playwright';

const paths = ( process.env.TEST_PATHS || '/' ).split( /\s+/ ).filter( Boolean );
const failOn = ( process.env.A11Y_IMPACT || 'critical serious' ).split( /\s+/ ).filter( Boolean );
const exclude = [ '#dev-info-panel', ...( process.env.A11Y_EXCLUDE || '' ).split( /\s+/ ).filter( Boolean ) ];

const TAGS = [ 'wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa' ];

// mobile shows other elements (e.g. menu toggle)
const VIEWPORTS = {
	mobile: { width: 390, height: 844 },
	desktop: { width: 1440, height: 900 },
};

// `rule (impact): help – 3× selector, …`
const format = violation => {
	const targets = violation.nodes.map( node => node.target.join( ' ' ) );
	return `${violation.id} (${violation.impact}): ${violation.help} – ${targets.length}× ${targets.slice( 0, 3 ).join( ', ' )}${targets.length > 3 ? ', …' : ''}`;
};

for ( const [ viewport, size ] of Object.entries( VIEWPORTS ) ) for ( const path of paths ) {
	test( `a11y ${viewport} ${path}`, async ( { page } ) => {
		await page.setViewportSize( size );
		await page.goto( path );
		// contrast is measured wrong mid-transition: wait for finite animations
		await page.evaluate( () => Promise.race( [
			Promise.all( document.getAnimations().filter( animation => animation.effect?.getTiming().iterations !== Infinity ).map( animation => animation.finished.catch( () => {} ) ) ),
			new Promise( resolve => setTimeout( resolve, 3000 ) ),
		] ) );

		let builder = new AxeBuilder( { page } ).withTags( TAGS );
		for ( const selector of exclude ) builder = builder.exclude( selector );
		const { violations, incomplete } = await builder.analyze();

		const errors = violations.filter( violation => failOn.includes( violation.impact ) ).map( format );
		for ( const warning of violations.filter( violation => !failOn.includes( violation.impact ) ).map( format ) ) {
			test.info().annotations.push( { type: 'warning', description: warning } );
			console.warn( `warn  ${warning}` );
		}
		for ( const check of incomplete.map( format ) ) test.info().annotations.push( { type: 'review', description: check } );
		expect( errors, 'accessibility violations (details: https://dequeuniversity.com/rules/axe/)' ).toEqual( [] );
	} );
}
