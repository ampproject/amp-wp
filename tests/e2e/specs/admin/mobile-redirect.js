/**
 * WordPress dependencies
 */
const { visitAdminPage } = require( '@wordpress/e2e-test-utils/build/visit-admin-page' );

/**
 * Internal dependencies
 */
const { completeWizard, cleanUpSettings } = require( '../../utils/onboarding-wizard-utils' );

const toggleSelector = '.amp-setting-toggle input[type="checkbox"]';

describe( 'Mobile redirect settings', () => {
	afterEach( async () => {
		await visitAdminPage( 'admin.php', 'page=amp-onboarding-wizard' );
		await cleanUpSettings();
	} );

	it( 'persists the mobile redirect setting on', async () => {
		await completeWizard( { mode: 'reader', mobileRedirect: true } );
		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await expect( page ).toClick( '.advanced-settings-container' );
		await page.waitForSelector( toggleSelector );
		await expect( page ).toMatchElement( `${ toggleSelector }:checked` );
	} );

	// eslint-disable-next-line jest/no-disabled-tests
	it.skip( 'persists the mobile redirect setting off', async () => {
		await completeWizard( { mode: 'reader', mobileRedirect: false } );
		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await expect( page ).toClick( '.advanced-settings-container' );
		await page.waitForSelector( toggleSelector );
		await expect( page ).not.toMatchElement( `${ toggleSelector }:checked` );
	} );
} );
