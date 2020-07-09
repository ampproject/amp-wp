/**
 * WordPress dependencies
 */
const { visitAdminPage } = require( '@wordpress/e2e-test-utils/build/visit-admin-page' );
/**
 * Internal dependencies
 */
const { goToOnboardingWizard, completeWizard, cleanUpSettings } = require( '../../utils/onboarding-wizard-utils' );

describe( 'Onboarding wizard exit links', () => {
	it( 'if no previous page, returns to settings when clicking close', async () => {
		await goToOnboardingWizard();
		await expect( page ).toClick( 'a', { text: 'Close' } );
		await page.waitForSelector( '.wp-admin' );
		await expect( page ).toMatchElement( 'h1', { text: 'AMP Settings' } );
	} );

	it( 'returns to previous page when clicking close', async () => {
		await visitAdminPage( 'index.php' );
		await page.waitForSelector( '.wp-admin' );

		await page.evaluate( () => {
			document.querySelector( 'a[href="admin.php?page=amp-onboarding-wizard"]' ).click();
		} );
		await page.waitForSelector( '#amp-onboarding-wizard' );
		await expect( page ).toClick( 'a', { text: 'Close' } );
		await page.waitForSelector( '.wp-admin' );
		await expect( page ).toMatchElement( 'h1', { text: 'Dashboard' } );
	} );

	it( 'goes to settings when clicking finish', async () => {
		await completeWizard( { mode: 'standard' } );
		await expect( page ).toClick( 'a', { text: 'Finish' } );
		await page.waitForSelector( '.wp-admin' );
		await expect( page ).toMatchElement( 'h1', { text: 'AMP Settings' } );

		await cleanUpSettings();
	} );
} );
