/**
 * WordPress dependencies
 */
import { visitAdminPage, activateTheme, installTheme } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { completeWizard, cleanUpSettings, clickMode, scrollToElement } from '../../utils/onboarding-wizard-utils';
import { cleanUpValidatedUrls, saveSettings } from '../../utils/amp-settings-utils';

describe( 'AMP settings screen newly activated', () => {
	beforeEach( async () => {
		await cleanUpSettings();
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

describe( 'AMP Settings Screen after wizard', () => {
	const timeout = 30000;

	beforeEach( async () => {
		await cleanUpValidatedUrls();
	} );

	afterEach( async () => {
		await cleanUpSettings();
	} );

	it( 'has main page components and does not display a stale message if the Standard mode was selected in the Wizard', async () => {
		await completeWizard( { technical: true, mode: 'standard' } );

		await expect( page ).toMatchElement( 'h1', { text: 'AMP Settings', timeout } );
		await expect( page ).toMatchElement( 'h2', { text: 'AMP Settings Configured' } );
		await expect( page ).toMatchElement( 'a', { text: 'Reopen Wizard' } );
		await expect( page ).toPassAxeTests( {
			exclude: [
				'#wpadminbar',
			],
		} );

		await expect( page ).toMatchElement( '#site-scan .amp-drawer__heading', { text: 'Site Scan' } );
		await expect( page ).not.toMatchElement( '#site-scan .amp-drawer__label-extra .amp-notice', { text: 'Stale results' } );
	} );

	it( 'auto-starts a site scan if Transitional mode was selected in the Wizard', async () => {
		await completeWizard( { technical: true, mode: 'transitional' } );

		await expect( page ).toMatchElement( '#site-scan .amp-drawer__heading', { text: 'Site Scan', timeout } );
		await expect( page ).toMatchElement( '#site-scan .progress-bar' );
		await expect( page ).toMatchElement( '#site-scan button', { text: 'Rescan Site', timeout } );
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
		// Save button exists.
		await expect( page ).toMatchElement( 'button[disabled]', { text: 'Save' } );

		// Toggle transitional mode.
		await clickMode( 'transitional' );

		// Button should be enabled.
		await expect( page ).toMatchElement( 'button:not([disabled])', { text: 'Save' } );

		await saveSettings();

		// Success notice should disappear on additional change.
		await clickMode( 'standard' );
		await expect( page ).not.toMatchElement( '.amp-save-success-notice', { text: 'Saved' } );

		await saveSettings();
	} );
} );
