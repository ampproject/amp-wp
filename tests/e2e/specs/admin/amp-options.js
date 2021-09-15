/**
 * WordPress dependencies
 */
import { visitAdminPage, activateTheme, installTheme } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { completeWizard, cleanUpSettings, clickMode, scrollToElement } from '../../utils/onboarding-wizard-utils';

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

		await scrollToElement( { selector: '#reader-themes .amp-notice__body' } );
		await expect( page ).toMatchElement( '.amp-notice__body', { text: /^Your active theme/ } );

		await activateTheme( 'twentytwenty' );
	} );
} );

describe( 'Mode info notices', () => {
	it( 'shows expected notices for theme with built-in support', async () => {
		await activateTheme( 'twentytwenty' );
		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await expect( page ).toMatchElement( '#template-mode-standard-container .amp-notice--info' );
		await expect( page ).toMatchElement( '#template-mode-transitional-container .amp-notice--info' );

		await clickMode( 'reader' );

		await expect( page ).toMatchElement( '#template-mode-reader-container .amp-notice--warning' );
	} );

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

describe( 'AMP settings screen Review panel', () => {
	beforeEach( async () => {
		await visitAdminPage( 'admin.php', 'page=amp-options' );
	} );

	afterEach( async () => {
		await cleanUpSettings();
	} );

	async function clickBrowseSiteInTemplateMode( mode ) {
		await clickMode( mode );
		await expect( page ).toClick( 'a', { text: 'Browse Site' } );
		await page.waitForNavigation();
	}

	it( 'is present on the page', async () => {
		await page.waitForSelector( '.settings-site-review' );
		await expect( page ).toMatchElement( 'h2', { text: 'Review' } );
		await expect( page ).toMatchElement( 'h3', { text: 'Need help?' } );
		await expect( page ).toMatchElement( '.settings-site-review__list li', { text: /support forums/i } );
		await expect( page ).toMatchElement( '.settings-site-review__list li', { text: /different template mode/i } );
		await expect( page ).toMatchElement( '.settings-site-review__list li', { text: /how the PX plugin works/i } );
	} );

	it( 'button redirects to an AMP page in transitional mode', async () => {
		await clickBrowseSiteInTemplateMode( 'transitional' );

		await page.waitForSelector( 'html[amp]' );
		await expect( page ).toMatchElement( 'html[amp]' );
	} );

	it( 'button redirects to an AMP page in reader mode', async () => {
		await clickBrowseSiteInTemplateMode( 'reader' );

		await page.waitForSelector( 'html[amp]' );
		await expect( page ).toMatchElement( 'html[amp]' );
	} );

	it( 'button redirects to an AMP page in standard mode', async () => {
		await clickBrowseSiteInTemplateMode( 'standard' );

		await page.waitForSelector( 'html[amp]' );
		await expect( page ).toMatchElement( 'html[amp]' );
	} );

	it( 'can be dismissed and shows up again only after a template mode change', async () => {
		await clickMode( 'transitional' );

		await expect( page ).toMatchElement( 'button', { text: 'Dismiss' } );
		await expect( page ).toClick( 'button', { text: 'Dismiss' } );
		await expect( page ).not.toMatchElement( 'h2', { text: 'Review' } );

		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await page.waitForSelector( '.settings-site-review' );
		await expect( page ).toMatchElement( 'h2', { text: 'Review' } );

		await clickMode( 'standard' );

		await expect( page ).toClick( 'button', { text: 'Save' } );
		await page.waitForSelector( '.amp-save-success-notice' );
		await expect( page ).toMatchElement( '.amp-save-success-notice', { text: 'Saved' } );

		await page.waitForSelector( '.settings-site-review' );
		await expect( page ).toMatchElement( 'h2', { text: 'Review' } );
	} );
} );
