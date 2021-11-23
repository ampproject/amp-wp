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
		await installPlugin( 'autoptimize' );
		await completeWizard( { technical: true, mode: 'transitional' } );
	} );

	afterAll( async () => {
		await uninstallPlugin( 'autoptimize' );
	} );

	beforeEach( async () => {
		await switchUserToAdmin();
		await visitAdminPage( 'plugins.php' );
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
		await expect( page ).toMatchElement( '#amp-site-scan-notice p', { text: /AMP plugin is checking your site for compatibility issues/ } );
		await expect( page ).toMatchElement( '#amp-site-scan-notice p', { text: /AMP plugin found no validation errors/, timeout } );
		await expect( page ).toMatchElement( '#amp-site-scan-notice .admin-notice--success' );
		await expect( page ).not.toMatchElement( '#amp-site-scan-notice summary' );
		await expect( page ).not.toMatchElement( '#amp-site-scan-notice .amp-site-scan-notice__cta' );
	} );

	it( 'site scan is triggered automatically and returns validation issues for Autoptimize', async () => {
		// Activate Autoptimize.
		await page.click( `tr[data-slug="autoptimize"] .activate a` );
		await page.waitForSelector( `tr[data-slug="autoptimize"] .deactivate a` );

		await expect( page ).toMatchElement( '#amp-site-scan-notice' );
		await expect( page ).toMatchElement( '#amp-site-scan-notice p', { text: /AMP plugin is checking your site for compatibility issues/ } );
		await expect( page ).toMatchElement( '#amp-site-scan-notice p', { text: /AMP Plugin found validation errors/, timeout } );
		await expect( page ).toMatchElement( '#amp-site-scan-notice .admin-notice--warning' );
		await expect( page ).toMatchElement( '#amp-site-scan-notice summary', { text: /Validation issues caused by Autoptimize/ } );
		await expect( page ).toMatchElement( '#amp-site-scan-notice .button', { text: /View Compatible Plugins List/ } );

		// Deactivate Autoptimize.
		await page.click( `tr[data-slug="autoptimize"] .deactivate a` );
		await page.waitForSelector( `tr[data-slug="autoptimize"] .delete a` );

		await expect( page ).not.toMatchElement( '#amp-site-scan-notice' );
	} );
} );
