/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { cleanUpSettings } from '../../utils/onboarding-wizard-utils';
import { setTemplateMode } from '../../utils/amp-settings-utils';
import { testSiteScanning } from '../../utils/site-scan-utils';

describe( 'AMP settings screen Site Scan panel', () => {
	beforeEach( async () => {
		await visitAdminPage( 'admin.php', 'page=amp-options' );
	} );

	afterEach( async () => {
		await cleanUpSettings();
	} );

	it( 'is present on the page and allows triggering a site scan', async () => {
		await page.waitForSelector( '#template-modes' );

		// Switch template mode so that we have stale results for sure.
		const selectedMode = await page.$eval( '#template-modes input[checked]', ( el ) => el.getAttribute( 'id' ) );
		await setTemplateMode( selectedMode.includes( 'transitional' ) ? 'standard' : 'transitional' );

		await page.waitForSelector( '.settings-site-scan' );
		await expect( page ).toMatchElement( 'h2', { text: 'Site Scan' } );
		await expect( page ).toMatchElement( 'button.is-primary', { text: 'Rescan Site' } );

		// Start site scan.
		await expect( page ).toClick( 'button.is-primary', { text: 'Rescan Site' } );

		await testSiteScanning( {
			statusElementClassName: 'settings-site-scan__status',
			isAmpFirst: false,
		} );

		await page.waitForSelector( '.settings-site-scan__footer a.is-primary' );
		await expect( page ).toMatchElement( '.settings-site-scan__footer a.is-primary', { text: 'Browse Site' } );
	} );
} );
