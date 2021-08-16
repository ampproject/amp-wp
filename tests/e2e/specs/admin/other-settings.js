/**
 * WordPress dependencies
 */
const { visitAdminPage } = require( '@wordpress/e2e-test-utils/build/visit-admin-page' );

/**
 * Internal dependencies
 */
const { cleanUpSettings, scrollToElement, completeWizard } = require( '../../utils/onboarding-wizard-utils' );

const panelSelector = '#other-settings .components-panel__body-toggle';

describe( 'Other settings', () => {
	beforeEach( async () => {
		await completeWizard( { mode: 'transitional' } );
		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await page.waitForSelector( panelSelector );
		await scrollToElement( { selector: panelSelector, click: true } );
	} );

	afterEach( async () => {
		await cleanUpSettings();
	} );

	it( 'persists the mobile redirect setting value', async () => {
		const toggleSelector = '#other-settings .mobile-redirection .amp-setting-toggle input[type="checkbox"]';

		// Confirm the mobile redirection is initially disabled.
		await page.waitForSelector( toggleSelector );
		await expect( page ).toMatchElement( `${ toggleSelector }:checked` );

		// Disable the setting and save.
		await page.waitForSelector( toggleSelector );
		await scrollToElement( { selector: toggleSelector, click: true } );
		await expect( page ).toMatchElement( `${ toggleSelector }:not(:checked)` );
		await scrollToElement( { selector: '.amp-settings-nav button[type="submit"]', click: true } );

		// Refresh the page once the settings have been saved.
		await page.waitForTimeout( 1000 );
		await visitAdminPage( 'admin.php', 'page=amp-options' );

		// Confirm the mobile redirection setting is persisted.
		await page.waitForSelector( panelSelector );
		await scrollToElement( { selector: panelSelector, click: true } );
		await page.waitForSelector( toggleSelector );
		await expect( page ).toMatchElement( `${ toggleSelector }:not(:checked)` );
	} );

	it( 'persists the dev tools setting value', async () => {
		const toggleSelector = '#other-settings .developer-tools .amp-setting-toggle input[type="checkbox"]';

		// Confirm the dev tools setting is enabled initially.
		await page.waitForSelector( toggleSelector );
		await expect( page ).toMatchElement( `${ toggleSelector }:checked` );

		// Disable the setting and save.
		await page.waitForSelector( toggleSelector );
		await scrollToElement( { selector: toggleSelector, click: true } );
		await expect( page ).toMatchElement( `${ toggleSelector }:not(:checked)` );
		await scrollToElement( { selector: '.amp-settings-nav button[type="submit"]', click: true } );

		// Refresh the page once the settings have been saved.
		await page.waitForTimeout( 1000 );
		await visitAdminPage( 'admin.php', 'page=amp-options' );

		// Confirm the dev tools setting is persisted.
		await page.waitForSelector( panelSelector );
		await scrollToElement( { selector: panelSelector, click: true } );
		await page.waitForSelector( toggleSelector );
		await expect( page ).toMatchElement( `${ toggleSelector }:not(:checked)` );
	} );
} );
