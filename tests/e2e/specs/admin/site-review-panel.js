/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { cleanUpSettings, scrollToElement } from '../../utils/onboarding-wizard-utils';
import { setTemplateMode } from '../../utils/amp-settings-utils';

describe( 'AMP settings screen Review panel', () => {
	const timeout = 30000;

	beforeAll( async () => {
		await cleanUpSettings();
	} );

	beforeEach( async () => {
		await visitAdminPage( 'admin.php', 'page=amp-options' );
	} );

	afterEach( async () => {
		await cleanUpSettings();
	} );

	it( 'is present on the page', async () => {
		await page.waitForSelector( '.settings-site-review' );
		await expect( page ).toMatchElement( 'h2', { text: 'Review' } );
		await expect( page ).toMatchElement( 'h3', { text: 'Need help?' } );
		await expect( page ).toMatchElement( '.settings-site-review__list li', { text: /support forums/i } );
		await expect( page ).toMatchElement( '.settings-site-review__list li', { text: /different template mode/i } );
		await expect( page ).toMatchElement( '.settings-site-review__list li', { text: /how the AMP plugin works/i } );
	} );

	it( 'button redirects to an AMP page in transitional mode', async () => {
		await setTemplateMode( 'transitional' );

		await Promise.all( [
			scrollToElement( { selector: '.settings-site-review__actions .is-primary', click: true, timeout } ),
			page.waitForNavigation( { timeout } ),
		] );

		const htmlAttributes = await page.$eval( 'html', ( el ) => el.getAttributeNames() );
		await expect( htmlAttributes ).toContain( 'amp' );
	} );

	it( 'button redirects to an AMP page in reader mode', async () => {
		await Promise.all( [
			scrollToElement( { selector: '.settings-site-review__actions .is-primary', click: true, timeout } ),
			page.waitForNavigation( { timeout } ),
		] );

		const htmlAttributes = await page.$eval( 'html', ( el ) => el.getAttributeNames() );
		await expect( htmlAttributes ).toContain( 'amp' );
	} );

	it( 'button redirects to an AMP page in standard mode', async () => {
		await setTemplateMode( 'standard' );

		await Promise.all( [
			scrollToElement( { selector: '.settings-site-review__actions .is-primary', click: true, timeout } ),
			page.waitForNavigation( { timeout } ),
		] );

		const htmlAttributes = await page.$eval( 'html', ( el ) => el.getAttributeNames() );
		await expect( htmlAttributes ).toContain( 'amp' );
	} );

	it( 'can be dismissed and shows up again only after a template mode change', async () => {
		const dismissButtonSelector = '.settings-site-review__actions button.is-link';

		await page.waitForSelector( dismissButtonSelector );

		// Click the "Dismiss" button and wait for the HTTP response.
		await Promise.all( [
			scrollToElement( { selector: dismissButtonSelector, click: true } ),
			page.waitForResponse( ( response ) => response.url().includes( '/wp/v2/users/me' ) ),
		] );

		await expect( page ).not.toMatchElement( '.settings-site-review' );

		// There should be no Review panel after page reload.
		await visitAdminPage( 'admin.php', 'page=amp-options' );
		await page.waitForSelector( '#amp-settings-root' );
		await expect( page ).not.toMatchElement( '.settings-site-review' );

		await setTemplateMode( 'standard' );

		await page.waitForSelector( '.settings-site-review' );
		await expect( page ).toMatchElement( 'h2', { text: 'Review' } );
	} );
} );
