/**
 * WordPress dependencies
 */
const { visitAdminPage } = require( '@wordpress/e2e-test-utils/build/visit-admin-page' );

/**
 * Internal dependencies
 */
const { cleanUpSettings, scrollToElement } = require( '../../utils/onboarding-wizard-utils' );

const panelSelector = '#other-settings .components-panel__body-toggle';
const toggleSelector = '#other-settings .mobile-redirection .amp-setting-toggle input[type="checkbox"]';

describe( 'Mobile redirect settings', () => {
	it( 'persists the mobile redirect setting value', async () => {
		// Disable mobile redirection by calling the `cleanUpSettings` function.
		await cleanUpSettings();
		await visitAdminPage( 'admin.php', 'page=amp-options' );

		// Confirm the mobile redirection is disabled.
		await page.waitForSelector( panelSelector );
		await scrollToElement( { selector: panelSelector, click: true } );
		await page.waitForSelector( toggleSelector );
		await expect( page ).toMatchElement( `${ toggleSelector }:not(:checked)` );

		// Disable the setting and save.
		await page.waitForSelector( toggleSelector );
		await scrollToElement( { selector: toggleSelector, click: true } );
		await expect( page ).toMatchElement( `${ toggleSelector }:checked` );
		await scrollToElement( { selector: '.amp-settings-nav button[type="submit"]', click: true } );

		// Refresh the page once the settings have been saved.
		await page.waitForTimeout( 1000 );
		await visitAdminPage( 'admin.php', 'page=amp-options' );

		// Confirm the mobile redirection setting has been persisted.
		await page.waitForSelector( panelSelector );
		await scrollToElement( { selector: panelSelector, click: true } );
		await page.waitForSelector( toggleSelector );
		await expect( page ).toMatchElement( `${ toggleSelector }:checked` );
	} );
} );
