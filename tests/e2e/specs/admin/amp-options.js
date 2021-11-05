/**
 * WordPress dependencies
 */
import { visitAdminPage, activateTheme, installTheme } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { completeWizard, cleanUpSettings, clickMode, scrollToElement } from '../../utils/onboarding-wizard-utils';
import { cleanUpValidatedUrls, setTemplateMode } from '../../utils/amp-settings-utils';

describe( 'AMP settings screen newly activated', () => {
	beforeEach( async () => {
		await visitAdminPage( 'admin.php', 'page=amp-options' );
	} );

	it( 'should not display the old welcome notice', async () => {
		await expect( page ).not.toMatchElement( '.amp-welcome-notice h2', { text: 'Welcome to AMP for WordPress' } );
	} );

	it( 'has main page components', async () => {
		await expect( page ).toMatchElement( 'h1', { text: 'AMP Settings' } );
		await expect( page ).toMatchElement( 'h2', { text: 'Configure AMP' } );
		await expect( page ).toMatchElement( 'a', { text: 'Open Wizard' } );
		await expect( page ).toMatchElement( '.template-mode-option input:checked' );
		await expect( page ).toPassAxeTests( {
			exclude: [
				'#wpadminbar',
			],
		} );
	} );

	it( 'shows expected elements for standard mode', async () => {
		await clickMode( 'standard' );
		await expect( page ).toMatchElement( '#template-mode-standard:checked' );

		await expect( page ).not.toMatchElement( '.mobile-redirection' );
		await expect( page ).not.toMatchElement( '.reader-themes' );
	} );

	it( 'shows expected elements for transitional mode', async () => {
		await clickMode( 'transitional' );
		await expect( page ).toMatchElement( '#template-mode-transitional:checked' );

		await expect( page ).not.toMatchElement( '.reader-themes' );
	} );
} );

describe( 'Settings screen when reader theme is active theme', () => {
	it( 'disables reader theme if is currently active on site', async () => {
		await installTheme( 'twentynineteen' );
		await activateTheme( 'twentynineteen' );

		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await clickMode( 'reader' );
		await scrollToElement( { selector: '#template-mode-reader-container .components-panel__body-toggle', click: true } );

		await scrollToElement( { selector: '#reader-themes .components-panel__body-toggle', click: true } );
		await expect( page ).toMatchElement( '.amp-notice__body', { text: /^Your active theme/ } );

		await activateTheme( 'twentytwenty' );
	} );
} );

describe( 'Mode info notices', () => {
	const timeout = 30000;

	beforeEach( async () => {
		await cleanUpValidatedUrls();
		await cleanUpSettings();
	} );

	it( 'show information in the Template Mode section if site scan results are stale', async () => {
		await completeWizard( { technical: true, mode: 'transitional' } );

		// When there are no site scan results, no notice in the Template Mode section should be displayed.
		await expect( page ).toMatchElement( '#template-modes h2', { text: 'Template Mode', timeout } );
		await expect( page ).not.toMatchElement( '#template-modes h2 + .amp-notice--info' );

		// Trigger the site scan.
		await expect( page ).toMatchElement( '#site-scan .amp-drawer__heading', { text: 'Site Scan' } );
		await Promise.all( [
			scrollToElement( { selector: '.settings-site-scan__footer .is-primary', click: true } ),
			page.waitForSelector( '.settings-site-scan__status' ),
		] );
		await page.waitForSelector( '.settings-site-scan__footer .is-primary', { timeout } );

		// Change the template mode to make the scan results stale and confirm the notice is displayed.
		await setTemplateMode( 'standard' );
		await page.waitForSelector( '.settings-site-scan__footer .is-primary', { timeout } );
		await expect( page ).toMatchElement( '#template-modes h2 + .amp-notice--info' );
	} );

	it.todo( 'shows expected notices for theme with built-in support' );
	it.todo( 'shows expected notices for theme with paired flag false' );
	it.todo( 'shows expected notices for theme that only supports reader mode' );
} );

describe( 'AMP Settings Screen after wizard', () => {
	beforeEach( async () => {
		await completeWizard( { technical: true, mode: 'standard' } );
		await visitAdminPage( 'admin.php', 'page=amp-options' );
	} );

	afterEach( async () => {
		await cleanUpSettings();
	} );

	it( 'has main page components', async () => {
		await expect( page ).toMatchElement( 'h1', { text: 'AMP Settings' } );
		await expect( page ).toMatchElement( 'h2', { text: 'AMP Settings Configured' } );
		await expect( page ).toMatchElement( 'a', { text: 'Reopen Wizard' } );
		await expect( page ).toPassAxeTests( {
			exclude: [
				'#wpadminbar',
			],
		} );
	} );
} );

describe( 'Saving', () => {
	beforeEach( async () => {
		await visitAdminPage( 'admin.php', 'page=amp-options' );
	} );

	afterEach( async () => {
		await cleanUpSettings();
	} );

	it( 'allows saving', async () => {
		const testSave = async () => {
			await expect( page ).toClick( 'button', { text: 'Save' } );
			await expect( page ).toMatchElement( 'button[disabled]', { text: 'Save' } );
			await expect( page ).toMatchElement( '.amp-save-success-notice', { text: 'Saved' } );
		};

		// Save button exists.
		await expect( page ).toMatchElement( 'button[disabled]', { text: 'Save' } );

		// Toggle transitional mode.
		await clickMode( 'transitional' );

		// Button should be enabled.
		await expect( page ).toMatchElement( 'button:not([disabled])', { text: 'Save' } );

		await testSave();

		// Success notice should disappear on additional change.
		await clickMode( 'standard' );
		await expect( page ).not.toMatchElement( '.amp-save-success-notice', { text: 'Saved' } );

		await testSave();
	} );
} );
