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
	cleanUpValidatedUrls,
	deactivatePlugin,
	installPlugin,
	setTemplateMode,
	uninstallPlugin,
} from '../../utils/amp-settings-utils';
import { cleanUpSettings, completeWizard, scrollToElement } from '../../utils/onboarding-wizard-utils';
import { testSiteScanning } from '../../utils/site-scan-utils';

describe( 'AMP settings screen Site Scan panel', () => {
	const timeout = 10000;

	beforeAll( async () => {
		await installTheme( 'hestia' );
		await installPlugin( 'autoptimize' );

		await cleanUpSettings();

		await visitAdminPage( 'admin.php', 'page=amp-options' );
		await setTemplateMode( 'transitional' );
	} );

	afterAll( async () => {
		await deleteTheme( 'hestia', { newThemeSlug: 'twentytwenty' } );
		await uninstallPlugin( 'autoptimize' );

		await cleanUpSettings();
	} );

	async function triggerSiteRescan() {
		await expect( page ).toMatchElement( '#site-scan h2', { text: 'Site Scan' } );

		const isPanelCollapsed = await page.$eval( '#site-scan .components-panel__body-toggle', ( el ) => el.ariaExpanded === 'false' );
		if ( isPanelCollapsed ) {
			await scrollToElement( { selector: '#site-scan .components-panel__body-toggle', click: true } );
		}

		// Start the site scan.
		await Promise.all( [
			scrollToElement( { selector: '.settings-site-scan__footer button.is-primary', click: true } ),
			testSiteScanning( {
				statusElementClassName: 'settings-site-scan__status',
				isAmpFirst: false,
			} ),
		] );

		await expect( page ).toMatchElement( '.settings-site-scan__footer .is-primary', { text: 'Rescan Site', timeout } );
		await expect( page ).toMatchElement( '.settings-site-scan__footer .is-link', { text: 'Browse Site' } );
	}

	it( 'does not list issues if an AMP compatible theme is activated', async () => {
		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await triggerSiteRescan();

		await expect( page ).toMatchElement( '.settings-site-scan .amp-notice--success', { timeout } );

		await expect( page ).not.toMatchElement( '.site-scan-results--themes' );
		await expect( page ).not.toMatchElement( '.site-scan-results--plugins' );

		// Reload the page and confirm that the panel is collapsed.
		await page.reload();
		await expect( page ).toMatchElement( '#site-scan .components-panel__body-toggle[aria-expanded="false"]' );

		// Switch template mode to check if the scan results are marked as stale and the panel is initially expanded.
		await setTemplateMode( 'standard' );

		await expect( page ).toMatchElement( '#site-scan .components-panel__body-toggle[aria-expanded="true"]', { timeout } );
		await expect( page ).toMatchElement( '.settings-site-scan .amp-notice--info', { text: /^Stale results/ } );
	} );

	it( 'lists Hestia theme as causing AMP incompatibility', async () => {
		await activateTheme( 'hestia' );

		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await triggerSiteRescan();

		await expect( page ).toMatchElement( '.site-scan-results--themes .site-scan-results__heading[data-badge-content="1"]', { text: /^Themes/, timeout } );
		await expect( page ).toMatchElement( '.site-scan-results--themes .site-scan-results__source-name', { text: /Hestia/ } );
	} );

	it( 'lists Autoptimize plugin as causing AMP incompatibility', async () => {
		await activateTheme( 'twentytwenty' );
		await activatePlugin( 'autoptimize' );

		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await triggerSiteRescan();

		await expect( page ).toMatchElement( '.site-scan-results--plugins .site-scan-results__heading[data-badge-content="1"]', { text: /^Plugins/, timeout } );
		await expect( page ).toMatchElement( '.site-scan-results--plugins .site-scan-results__source-name', { text: /Autoptimize/ } );

		await expect( page ).not.toMatchElement( '.site-scan-results--themes' );

		await deactivatePlugin( 'autoptimize' );
	} );

	it( 'lists Hestia theme and Autoptimize plugin for causing AMP incompatibilities', async () => {
		await activateTheme( 'hestia' );
		await activatePlugin( 'autoptimize' );

		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await triggerSiteRescan();

		await expect( page ).toMatchElement( '.site-scan-results--themes', { timeout } );
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

		await expect( page ).toMatchElement( '.site-scan-results--plugins .site-scan-results__source-name', { text: /Autoptimize/, timeout } );

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

describe( 'after completing the Onboarding Wizard and landing on the AMP Settings screen', () => {
	beforeEach( async () => {
		await cleanUpValidatedUrls();
		await cleanUpSettings();
	} );

	it( 'the Site Scan panel does not display a stale message if the Standard mode was selected in the Wizard', async () => {
		await completeWizard( { technical: true, mode: 'standard' } );
		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await expect( page ).toMatchElement( '#site-scan .amp-drawer__heading', { text: 'Site Scan' } );
		await expect( page ).not.toMatchElement( '#site-scan .amp-drawer__label-extra .amp-notice', { text: 'Stale results' } );
	} );

	it( 'the Site Scan panel displays a stale message if the Transitional mode was selected in the Wizard', async () => {
		await completeWizard( { technical: true, mode: 'transitional' } );
		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await expect( page ).toMatchElement( '#site-scan .amp-drawer__heading', { text: 'Site Scan' } );
		await expect( page ).toMatchElement( '#site-scan .amp-drawer__label-extra .amp-notice', { text: 'Stale results' } );
	} );
} );
