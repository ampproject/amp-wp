/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { cleanUpSettings, clickMode, scrollToElement } from '../../utils/onboarding-wizard-utils';
import { setTemplateMode } from '../../utils/amp-settings-utils';

describe( 'AMP settings screen Review panel', () => {
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

	it.each( [
		'transitional',
		'standard',
		'reader',
	] )( 'button redirects to an AMP page in %s mode', async ( mode ) => {
		// Make sure the template mode needs to be changed for a test.
		await page.waitForSelector( '#template-modes' );

		const selectedMode = await page.$eval( '#template-modes input[checked]', ( el ) => el.getAttribute( 'id' ) );

		if ( ! selectedMode.includes( mode ) ) {
			await setTemplateMode( mode );
		}

		// Click "Browse Site" button.
		const selector = '.settings-site-review__actions a.is-primary';

		await page.waitForSelector( selector );
		await scrollToElement( { selector, click: true } );

		await page.waitForNavigation();

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

		await clickMode( 'standard' );

		await expect( page ).toClick( 'button', { text: 'Save' } );
		await page.waitForSelector( '.amp-save-success-notice' );
		await expect( page ).toMatchElement( '.amp-save-success-notice', { text: 'Saved' } );

		await page.waitForSelector( '.settings-site-review' );
		await expect( page ).toMatchElement( 'h2', { text: 'Review' } );
	} );
} );
