/**
 * WordPress dependencies
 */
const { visitAdminPage } = require( '@wordpress/e2e-test-utils/build/visit-admin-page' );

/**
 * Internal dependencies
 */
const { completeWizard, cleanUpWizard } = require( '../../utils/onboarding-wizard-utils' );

describe( 'Mobile redirect settings', () => {
	afterEach( async () => {
		await visitAdminPage( 'admin.php', 'page=amp-setup' );
		await cleanUpWizard();
	} );

	it( 'persists the mobile redirect setting on', async () => {
		await completeWizard( { mode: 'reader' } );
		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await page.waitForSelector( '#mobile_redirect' );
		await expect( page ).toMatchElement( '#mobile_redirect:checked' );
	} );

	it( 'persists the mobile redirect setting off', async () => {
		await completeWizard( { mode: 'reader', mobileRedirect: false } );
		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await page.waitForSelector( '#mobile_redirect' );
		await expect( page ).not.toMatchElement( '#mobile_redirect:checked' );
	} );
} );
