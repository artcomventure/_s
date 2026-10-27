/**
 * Mail test (dev only): fills and sends Contact Form 7 forms, checks the mail arrives in Mailpit.
 *
 * `MAIL_FORMS="/contact/ /registration/"` – pages with a CF7 form (no default: skipped without).
 * `MAILPIT_URL=http://localhost:8025` – Mailpit (default).
 * `MAIL_DELAY=5` – seconds before submit, spam protection rejects faster forms (default `5`, CF7 honeypot time check: 4).
 */

import { test, expect } from '@playwright/test';

const forms = ( process.env.MAIL_FORMS || '' ).split( /\s+/ ).filter( Boolean );
const mailpit = ( process.env.MAILPIT_URL || 'http://localhost:8025' ).replace( /\/$/, '' );
const delay = Number( process.env.MAIL_DELAY || 5 ) * 1000;

// values set by script, not typed: consent dialogs trap the focus, custom dropdowns hide selects
// skips hidden fields and honeypots (spam protection)
const fill = ( form, token ) => form.evaluate( ( form, token ) => {
	const values = {
		email: `${token}@example.com`,
		tel: '0301234567',
		number: '1',
		url: 'https://example.com',
		date: '2030-01-01',
	};
	for ( const field of form.querySelectorAll( 'input, select, textarea' ) ) {
		const type = field.type.toLowerCase();
		if ( field.closest( '[class*="honeypot"]' ) || type === 'hidden' || field.disabled ) continue;
		if ( field.tagName !== 'SELECT' && !field.checkVisibility() ) continue;

		if ( field.tagName === 'SELECT' ) field.value = [ ...field.options ].find( option => option.value && !option.disabled )?.value ?? '';
		else if ( [ 'checkbox', 'radio' ].includes( type ) ) field.checked = true;
		else if ( type in values ) field.value = values[type];
		else if ( field.tagName === 'TEXTAREA' || [ 'text', 'search' ].includes( type ) ) field.value = token;
		else continue;

		field.dispatchEvent( new Event( 'input', { bubbles: true } ) );
		field.dispatchEvent( new Event( 'change', { bubbles: true } ) );
	}
}, token );

const findMails = async token => {
	const response = await fetch( `${mailpit}/api/v1/search?query=${encodeURIComponent( `"${token}"` )}` );
	return ( await response.json() ).messages || [];
};

test.beforeAll( async () => {
	const reachable = await fetch( `${mailpit}/api/v1/info` ).then( response => response.ok, () => false );
	test.skip( !reachable, `Mailpit not reachable (${mailpit})` );
} );

for ( const path of forms ) {
	test( `mail ${path} @write`, async ( { page } ) => {
		test.setTimeout( delay + 30000 );
		const token = `playwright-${Date.now()}`;
		await page.goto( path );

		const form = page.locator( 'form.wpcf7-form' ).first();
		await fill( form, token );
		await page.waitForTimeout( delay );
		// `<button>` without type submits too; event instead of mouse click: overlays (consent banner) don't matter here
		const [ response ] = await Promise.all( [
			page.waitForResponse( response => /contact-form-7\/v1\/contact-forms\/\d+\/feedback$/.test( response.url() ) && response.request().method() === 'POST' ),
			form.locator( '[type="submit"], button:not([type])' ).first().dispatchEvent( 'click' ),
		] );
		const result = await response.json();
		expect( result.status, `CF7 ${result.status}: ${result.message} ${JSON.stringify( result.invalid_fields || [] )}` ).toBe( 'mail_sent' );
		await expect.poll( async () => ( await findMails( token ) ).length, { message: 'mail in Mailpit', timeout: 10000 } ).toBeGreaterThan( 0 );

		// cleanup
		const ids = ( await findMails( token ) ).map( message => message.ID );
		await fetch( `${mailpit}/api/v1/messages`, { method: 'DELETE', body: JSON.stringify( { IDs: ids } ) } );
	} );
}
