/**
 * WordPress dependencies
 */
const { visitAdminPage } = require( '@wordpress/e2e-test-utils/build/visit-admin-page' );
/**
 * Internal dependencies
 */
const { goToOnboardingWizard, cleanUpSettings, moveToDoneScreen } = require( '../../utils/onboarding-wizard-utils' );

describe( 'Onboarding wizard exit links', () => {
	it( 'if no previous page, returns to settings when clicking close', async () => {
		await goToOnboardingWizard();
		await expect( page ).toClick( 'a', { text: 'Close' } );
		await page.waitForSelector( '.wp-admin' );
		await expect( page ).toMatchElement( 'h1', { text: 'AMP Settings' } );
	} );

	it( 'returns to previous page when clicking close', async () => {
		await visitAdminPage( 'admin.php', 'page=amp-options' );
		await page.waitForSelector( '.wp-admin' );

		await page.waitForSelector( 'a[href*="admin.php?page=amp-onboarding-wizard"]' );
		await expect( page ).toClick( 'a[href*="admin.php?page=amp-onboarding-wizard"]' );
		await page.waitForSelector( '#amp-onboarding-wizard' );
		await expect( page ).toClick( 'a', { text: 'Close' } );
		await page.waitForSelector( '.wp-admin' );
		await expect( page ).toMatchElement( 'h1', { text: 'AMP Settings' } );
	} );

	it( 'goes to settings when clicking finish', async () => {
		await moveToDoneScreen( { mode: 'standard' } );
		await expect( page ).toClick( 'a', { text: 'Finish' } );
		await page.waitForSelector( '.wp-admin' );
		await expect( page ).toMatchElement( 'h1', { text: 'AMP Settings' } );

		await cleanUpSettings();
	} );
} );
