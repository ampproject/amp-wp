/**
 * WordPress dependencies
 */
import { switchUserToAdmin, visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { completeWizard } from '../../utils/onboarding-wizard-utils';
import { installPlugin, uninstallPlugin } from '../../utils/amp-settings-utils';

describe( 'After plugin activation', () => {
	const timeout = 30000;

	beforeAll( async () => {
		await completeWizard( { technical: true, mode: 'transitional' } );
	} );

	beforeEach( async () => {
		await switchUserToAdmin();
		await visitAdminPage( 'plugins.php', '' );
	} );

	it( 'site scan is triggered automatically and displays no validation issues for Gutenberg', async () => {
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

	it( 'site scan is triggered automatically and returns validation issues for E2E Tests Demo Plugin', async () => {
		// Activate the demo plugin.
		await page.click( `tr[data-slug="e2e-tests-demo-plugin"] .activate a` );
		await page.waitForSelector( `tr[data-slug="e2e-tests-demo-plugin"] .deactivate a` );

		await expect( page ).toMatchElement( '#amp-site-scan-notice' );
		await expect( page ).toMatchElement( '#amp-site-scan-notice p', { text: /Checking your site for AMP compatibility issues/ } );
		await expect( page ).toMatchElement( '#amp-site-scan-notice p', { text: /AMP Plugin found validation errors/, timeout } );
		await expect( page ).toMatchElement( '#amp-site-scan-notice .amp-admin-notice--warning' );
		await expect( page ).toMatchElement( '#amp-site-scan-notice summary', { text: /Validation issues caused by E2E Tests Demo Plugin/ } );
		await expect( page ).toMatchElement( '#amp-site-scan-notice .button', { text: /View AMP-Compatible Plugins/ } );

		// Deactivate the demo plugin.
		await page.click( `tr[data-slug="e2e-tests-demo-plugin"] .deactivate a` );
		await page.waitForSelector( `tr[data-slug="e2e-tests-demo-plugin"] .delete a` );

		await expect( page ).not.toMatchElement( '#amp-site-scan-notice' );
	} );
} );
