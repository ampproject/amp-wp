/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { completeWizard, cleanUpSettings, clickMode, selectReaderTheme } from '../../utils/onboarding-wizard-utils';
import { installTheme } from '../../utils/install-theme';
import { activateTheme } from '../../utils/activate-theme';

async function testStandardAndTransitionalSupportedTemplateToggle() {
	await expect( page ).toMatchElement( '.supported-templates' );
	await expect( page ).toClick( '.supported-templates .amp-setting-toggle input:checked' );
	await expect( page ).toMatchElement( '.supported-templates .amp-setting-toggle input:not(:checked)' );
	await expect( page ).not.toMatchElement( '#supported_templates_fieldset.hidden' );
	await expect( page ).toMatchElement( '#amp-options-supported-templates-is_author:not(:checked)' );
	await expect( page ).toClick( '#amp-options-supported-templates-is_archive:not(:checked)' );
	await expect( page ).toMatchElement( '#amp-options-supported-templates-is_archive:checked' );
	await expect( page ).toMatchElement( '#amp-options-supported-templates-is_author:checked' );
}

async function testMobileRedirectToggle() {
	await expect( page ).toMatchElement( '.mobile-redirection' );
	await expect( page ).toClick( '.mobile-redirection .amp-setting-toggle input:not(:checked)' );
	await expect( page ).toMatchElement( '.mobile-redirection .amp-setting-toggle input:checked' );
}

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
		await expect( page ).toMatchElement( '.template-mode-selection input:checked' );
		await expect( page ).toPassAxeTests( {
			exclude: [
				'#wpadminbar',
			],
		} );
	} );

	it( 'shows expected elements for standard mode', async () => {
		await clickMode( 'standard' );
		await expect( page ).toMatchElement( '#template-mode-standard:checked' );

		await testStandardAndTransitionalSupportedTemplateToggle();

		await expect( page ).not.toMatchElement( '.mobile-redirection' );
		await expect( page ).not.toMatchElement( '.reader-themes' );
	} );

	it( 'shows expected elements for transitional mode', async () => {
		await clickMode( 'transitional' );
		await expect( page ).toMatchElement( '#template-mode-transitional:checked' );

		await testStandardAndTransitionalSupportedTemplateToggle();
		await testMobileRedirectToggle();
		await expect( page ).not.toMatchElement( '.reader-themes' );
	} );

	it( 'shows expected elements for reader mode', async () => {
		await clickMode( 'reader' );
		await expect( page ).toMatchElement( '#template-mode-reader:checked' );

		await testMobileRedirectToggle();

		await expect( page ).toMatchElement( '.reader-themes' );
		await expect( page ).toMatchElement( '#theme-card__legacy:checked' );

		await expect( page ).not.toMatchElement( '#theme-card__twentytwenty:checked' ); // Active theme is hidden.

		await selectReaderTheme( 'twentynineteen' );
		await expect( page ).toMatchElement( '#theme-card__twentynineteen:checked' );
	} );
} );

describe( 'Settings screen when reader theme is active theme', () => {
	it( 'disables reader theme if is currently active on site', async () => {
		await installTheme( 'twentynineteen' );
		await activateTheme( 'twentynineteen' );

		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await clickMode( 'reader' );

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

		await expect( page ).toMatchElement( '#template-mode-reader-container .amp-notice--info' );
	} );

	it.todo( 'shows expected notices for theme with paired flag false' );
	it.todo( 'shows expected notices for theme that only supports reader mode' );
} );

describe( 'AMP Settings Screen after wizard', () => {
	beforeAll( async () => {
		await completeWizard( { technical: true, mode: 'standard' } );
		await visitAdminPage( 'admin.php', 'page=amp-options' );
	} );

	afterAll( async () => {
		await cleanUpSettings();
	} );

	it( 'has main page components', async () => {
		await expect( page ).not.toMatchElement( '.amp-plugin-notice' );
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

