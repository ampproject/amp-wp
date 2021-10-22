/**
 * WordPress dependencies
 */
import {
	activateTheme,
	deleteTheme,
	installTheme,
	visitAdminPage,
} from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import {
	activatePlugin,
	deactivatePlugin,
	installPlugin,
	setTemplateMode,
	uninstallPlugin,
} from '../../utils/amp-settings-utils';
import { cleanUpSettings, scrollToElement } from '../../utils/onboarding-wizard-utils';
import { testSiteScanning } from '../../utils/site-scan-utils';

describe( 'AMP settings screen Site Scan panel', () => {
	beforeAll( async () => {
		await installTheme( 'hestia' );
		await installPlugin( 'autoptimize' );
	} );

	afterAll( async () => {
		await cleanUpSettings();
		await deleteTheme( 'hestia', { newThemeSlug: 'twentytwenty' } );
		await uninstallPlugin( 'autoptimize' );
	} );

	beforeEach( async () => {
		await cleanUpSettings();
	} );

	async function triggerSiteRescan() {
		await page.waitForSelector( '#template-modes' );

		// Switch template mode so that we have stale results for sure.
		await setTemplateMode( 'transitional' );

		await page.waitForSelector( '.settings-site-scan' );
		await expect( page ).toMatchElement( 'h2', { text: 'Site Scan' } );

		// Start the site scan.
		await Promise.all( [
			scrollToElement( { selector: '.settings-site-scan__footer button.is-primary', click: true } ),
			testSiteScanning( {
				statusElementClassName: 'settings-site-scan__status',
				isAmpFirst: false,
			} ),
			expect( page ).toMatchElement( '.settings-site-scan__footer a.is-primary', { text: 'Browse Site', timeout: 10000 } ),
		] );
	}

	it( 'does not list issues if an AMP compatible theme is activated', async () => {
		await activateTheme( 'twentytwenty' );

		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await triggerSiteRescan();

		await expect( page ).toMatchElement( '.settings-site-scan .amp-notice--success' );

		await expect( page ).not.toMatchElement( '.site-scan-results--themes' );
		await expect( page ).not.toMatchElement( '.site-scan-results--plugins' );

		// Switch template mode to check if the scan results are marked as stale.
		await setTemplateMode( 'standard' );

		await expect( page ).toMatchElement( '.settings-site-scan .amp-notice--info', { text: /^Stale results/ } );
	} );

	it( 'lists Hestia theme as causing AMP incompatibility', async () => {
		await activateTheme( 'hestia' );

		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await expect( page ).toMatchElement( '.settings-site-scan .amp-notice--info', { text: /^Stale results/ } );

		await triggerSiteRescan();

		await expect( page ).toMatchElement( '.site-scan-results--themes .site-scan-results__heading[data-badge-content="1"]', { text: /^Themes/ } );
		await expect( page ).toMatchElement( '.site-scan-results--themes .site-scan-results__source-name', { text: /Hestia/ } );
	} );

	it( 'lists Autoptimize plugin as causing AMP incompatibility', async () => {
		await activateTheme( 'twentytwenty' );
		await activatePlugin( 'autoptimize' );

		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await expect( page ).toMatchElement( '.settings-site-scan .amp-notice--info', { text: /^Stale results/ } );

		await triggerSiteRescan();

		await expect( page ).not.toMatchElement( '.site-scan-results--themes' );

		await expect( page ).toMatchElement( '.site-scan-results--plugins .site-scan-results__heading[data-badge-content="1"]', { text: /^Plugins/ } );
		await expect( page ).toMatchElement( '.site-scan-results--plugins .site-scan-results__source-name', { text: /Autoptimize/ } );

		await deactivatePlugin( 'autoptimize' );
	} );

	it( 'lists Hestia theme and Autoptimize plugin for causing AMP incompatibilities', async () => {
		await activateTheme( 'hestia' );
		await activatePlugin( 'autoptimize' );

		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await expect( page ).toMatchElement( '.settings-site-scan .amp-notice--info', { text: /^Stale results/ } );

		await triggerSiteRescan();

		await expect( page ).toMatchElement( '.site-scan-results--themes' );
		await expect( page ).toMatchElement( '.site-scan-results--plugins' );

		const totalIssuesCount = await page.$$eval( '.site-scan-results__source', ( sources ) => sources.length );
		expect( totalIssuesCount ).toBe( 2 );

		await expect( page ).toMatchElement( '.site-scan-results--themes .site-scan-results__source-name', { text: /Hestia/ } );
		await expect( page ).toMatchElement( '.site-scan-results--plugins .site-scan-results__source-name', { text: /Autoptimize/ } );

		await deactivatePlugin( 'autoptimize' );
	} );

	it( 'displays a notice if a plugin has been deactivated or removed', async () => {
		await activateTheme( 'twentytwenty' );
		await activatePlugin( 'autoptimize' );

		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await triggerSiteRescan();

		await expect( page ).toMatchElement( '.site-scan-results--plugins .site-scan-results__source-name', { text: /Autoptimize/ } );

		// Deactivate the plugin and test.
		await deactivatePlugin( 'autoptimize' );

		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await expect( page ).toMatchElement( '.site-scan-results--plugins .site-scan-results__source-name', { text: /Autoptimize/ } );
		await expect( page ).toMatchElement( '.site-scan-results--plugins .site-scan-results__source-notice', { text: /This plugin has been deactivated since last site scan./ } );

		// Uninstall the plugin and test.
		await uninstallPlugin( 'autoptimize' );

		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await expect( page ).toMatchElement( '.site-scan-results--plugins .site-scan-results__source-slug', { text: /autoptimize/ } );
		await expect( page ).toMatchElement( '.site-scan-results--plugins .site-scan-results__source-notice', { text: /This plugin has been uninstalled since last site scan./ } );

		// Clean up.
		await installPlugin( 'autoptimize' );
	} );
} );
