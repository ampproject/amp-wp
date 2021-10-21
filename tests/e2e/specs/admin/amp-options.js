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
	let testPost;

	beforeAll( async () => {
		await visitAdminPage( 'admin.php', 'page=amp-options' );

		testPost = await page.evaluate( () => wp.apiFetch( {
			path: '/wp/v2/posts',
			method: 'POST',
			data: { title: 'Test Post', status: 'publish' },
		} ) );
	} );

	afterAll( async () => {
		await visitAdminPage( 'admin.php', 'page=amp-options' );

		if ( testPost.id ) {
			await page.evaluate( ( id ) => wp.apiFetch( {
				path: `/wp/v2/posts/${ id }`,
				method: 'DELETE',
				data: { force: true },
			} ), testPost.id );
		}
	} );

	beforeEach( async () => {
		await visitAdminPage( 'admin.php', 'page=amp-options' );
	} );

	afterEach( async () => {
		await cleanUpSettings();
	} );

	async function changeAndSaveTemplateMode( mode ) {
		await clickMode( mode );

		await Promise.all( [
			scrollToElement( { selector: '.amp-settings-nav button[type="submit"]', click: true } ),
			page.waitForResponse( ( response ) => response.url().includes( '/wp-json/amp/v1/options' ), { timeout: 10000 } ),
		] );
	}

	it( 'is present on the page', async () => {
		await page.waitForSelector( '.settings-site-review' );
		await expect( page ).toMatchElement( 'h2', { text: 'Review' } );
		await expect( page ).toMatchElement( 'h3', { text: 'Need help?' } );
		await expect( page ).toMatchElement( '.settings-site-review__list li', { text: /support forums/i } );
		await expect( page ).toMatchElement( '.settings-site-review__list li', { text: /different template mode/i } );
		await expect( page ).toMatchElement( '.settings-site-review__list li', { text: /how the AMP plugin works/i } );
	} );

	it( 'button redirects to an AMP page in transitional mode', async () => {
		await changeAndSaveTemplateMode( 'transitional' );

		await expect( page ).toClick( 'a', { text: 'Browse Site' } );
		await page.waitForNavigation();

		await page.waitForSelector( 'html[amp]' );
		await expect( page ).toMatchElement( 'html[amp]' );
	} );

	it( 'button redirects to an AMP page in reader mode', async () => {
		await expect( page ).toClick( 'a', { text: 'Browse Site' } );
		await page.waitForNavigation();

		await page.waitForSelector( 'html[amp]' );
		await expect( page ).toMatchElement( 'html[amp]' );
	} );

	it( 'button redirects to an AMP page in standard mode', async () => {
		await changeAndSaveTemplateMode( 'standard' );

		await expect( page ).toClick( 'a', { text: 'Browse Site' } );
		await page.waitForNavigation();

		await page.waitForSelector( 'html[amp]' );
		await expect( page ).toMatchElement( 'html[amp]' );
	} );

	it( 'can be dismissed and shows up again only after a template mode change', async () => {
		await page.waitForSelector( '.settings-site-review' );
		await expect( page ).toMatchElement( 'button', { text: 'Dismiss' } );
		await expect( page ).toClick( 'button', { text: 'Dismiss' } );

		// Give the Review panel some time disappear.
		await page.waitForTimeout( 100 );
		await expect( page ).not.toMatchElement( '.settings-site-review' );

		// There should be no Review panel after page reload.
		await visitAdminPage( 'admin.php', 'page=amp-options' );
		await page.waitForSelector( '#amp-settings-root' );
		await expect( page ).not.toMatchElement( '.settings-site-review' );

		await changeAndSaveTemplateMode( 'standard' );

		await page.waitForSelector( '.settings-site-review' );
		await expect( page ).toMatchElement( 'h2', { text: 'Review' } );
	} );
} );
