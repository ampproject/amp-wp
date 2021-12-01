/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { completeWizard } from '../../utils/onboarding-wizard-utils';

describe( 'After plugin activation for a technical user', () => {
	const timeout = 30000;

	beforeAll( async () => {
		await completeWizard( { technical: true, mode: 'transitional' } );
		await visitAdminPage( 'plugins.php', '' );
	} );

	it( 'site scan is triggered automatically and displays no validation issues for AMP-compatible plugin', async () => {
		// Deactivate Gutenberg.
		await page.click( `tr[data-slug="gutenberg"] .deactivate a` );
		await page.waitForSelector( `tr[data-slug="gutenberg"] .delete a` );

		await expect( page ).not.toMatchElement( '#amp-site-scan-notice' );

		// Activate Gutenberg.
		await page.click( `tr[data-slug="gutenberg"] .activate a` );
		await page.waitForSelector( `tr[data-slug="gutenberg"] .deactivate a` );

		await expect( page ).toMatchElement( '#amp-site-scan-notice' );
		await expect( page ).toMatchElement( '#amp-site-scan-notice p', { text: /Checking your site for AMP compatibility issues/ } );
		await expect( page ).toMatchElement( '#amp-site-scan-notice p', { text: /No AMP compatibility issues detected/, timeout } );
		await expect( page ).toMatchElement( '#amp-site-scan-notice .amp-admin-notice--success' );
		await expect( page ).not.toMatchElement( '#amp-site-scan-notice summary' );
		await expect( page ).not.toMatchElement( '#amp-site-scan-notice .amp-site-scan-notice__cta' );
	} );

	it( 'site scan is triggered automatically and displays validation issues for AMP-incompatible plugin', async () => {
		// Activate the demo plugin.
		await page.click( `tr[data-slug="e2e-tests-demo-plugin"] .activate a` );
		await page.waitForSelector( `tr[data-slug="e2e-tests-demo-plugin"] .deactivate a` );

		await expect( page ).toMatchElement( '#amp-site-scan-notice' );
		await expect( page ).toMatchElement( '#amp-site-scan-notice p', { text: /Checking your site for AMP compatibility issues/ } );
		await expect( page ).toMatchElement( '#amp-site-scan-notice p', { text: /AMP compatibility issues discovered with the following plugin:/, timeout } );
		await expect( page ).toMatchElement( '#amp-site-scan-notice .amp-admin-notice--warning' );
		await expect( page ).toMatchElement( '#amp-site-scan-notice summary', { text: /E2E Tests Demo Plugin/ } );
		await expect( page ).toMatchElement( '#amp-site-scan-notice .button', { text: /Review Plugin Suppression/ } );
		await expect( page ).toMatchElement( '#amp-site-scan-notice .button', { text: /View AMP-Compatible Plugins/ } );

		// Deactivate the demo plugin.
		await page.click( `tr[data-slug="e2e-tests-demo-plugin"] .deactivate a` );
		await page.waitForSelector( `tr[data-slug="e2e-tests-demo-plugin"] .delete a` );

		await expect( page ).not.toMatchElement( '#amp-site-scan-notice' );
	} );
} );

describe( 'After plugin activation for a non-technical user', () => {
	const timeout = 30000;

	beforeAll( async () => {
		await completeWizard( { technical: false, mode: 'transitional' } );
		await visitAdminPage( 'plugins.php', '' );
	} );

	it( 'site scan is triggered automatically and displays only a name of a plugin causing AMP compatibility issues', async () => {
		// Activate the demo plugin.
		await page.click( `tr[data-slug="e2e-tests-demo-plugin"] .activate a` );
		await page.waitForSelector( `tr[data-slug="e2e-tests-demo-plugin"] .deactivate a` );

		await expect( page ).toMatchElement( '#amp-site-scan-notice' );
		await expect( page ).toMatchElement( '#amp-site-scan-notice p', { text: /AMP compatibility issues discovered with E2E Tests Demo Plugin/, timeout } );
		await expect( page ).toMatchElement( '#amp-site-scan-notice .amp-admin-notice--warning' );
		await expect( page ).not.toMatchElement( '#amp-site-scan-notice details' );
		await expect( page ).toMatchElement( '#amp-site-scan-notice .button', { text: /Review Plugin Suppression/ } );
		await expect( page ).toMatchElement( '#amp-site-scan-notice .button', { text: /View AMP-Compatible Plugins/ } );

		// Deactivate the demo plugin.
		await page.click( `tr[data-slug="e2e-tests-demo-plugin"] .deactivate a` );
		await page.waitForSelector( `tr[data-slug="e2e-tests-demo-plugin"] .delete a` );

		await expect( page ).not.toMatchElement( '#amp-site-scan-notice' );
	} );
} );
