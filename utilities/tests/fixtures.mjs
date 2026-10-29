/**
 * Shared `test`/`expect` for all specs (also project tests in `THEME/tests/`):
 * tracking requests get an empty response, so test runs don't count as visits (e.g. against `live`).
 * Requests still show up in `page.on( 'request' )` (privacy test).
 *
 * `TEST_BLOCK="stats.example.com /track.php"` – additional URL parts to block.
 */

import { test as base, expect } from '@playwright/test';

const TRACKING = [
	/\/(matomo|piwik)\.(php|js)(\?|$)/, // Matomo (also self-hosted)
	/(^|\.)google-analytics\.com\//,
	/(^|\.)analytics\.google\.com\//,
	/(^|\.)googletagmanager\.com\//,
	/(^|\.)stats\.wp\.com\//, // Jetpack
	/(^|\.)plausible\.io\//,
	/(^|\.)hotjar\.com\//,
	/(^|\.)clarity\.ms\//,
	/(^|\.)facebook\.com\/tr\b/, // Meta pixel
];
const extra = ( process.env.TEST_BLOCK || '' ).split( /\s+/ ).filter( Boolean );

const isTracking = url => TRACKING.some( pattern => pattern.test( url.host + url.pathname + url.search ) )
	|| extra.some( part => url.href.includes( part ) );

export const test = base.extend( {
	context: async ( { context }, use ) => {
		// `fulfill` instead of `abort`: no failed requests in the smoke test
		await context.route( isTracking, route => route.fulfill( { status: 204, body: '' } ) );
		await use( context );
	},
} );

export { expect };
