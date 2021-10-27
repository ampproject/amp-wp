/**
 * WordPress dependencies
 */
import {
	activateTheme,
	deleteTheme,
	installTheme,
} from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import {
	moveToSiteScanScreen,
	testNextButton,
	testPreviousButton,
} from '../../utils/onboarding-wizard-utils';
import { testSiteScanning } from '../../utils/site-scan-utils';
import {
	activatePlugin,
	deactivatePlugin,
	installPlugin,
	uninstallPlugin,
} from '../../utils/amp-settings-utils';

describe( 'Onboarding Wizard Site Scan Step', () => {
	beforeAll( async () => {
		await installTheme( 'hestia' );
		await installPlugin( 'autoptimize' );
	} );

	afterAll( async () => {
		await deleteTheme( 'hestia', { newThemeSlug: 'twentytwenty' } );
		await uninstallPlugin( 'autoptimize' );
	} );

	it( 'should start a site scan immediately', async () => {
		await moveToSiteScanScreen( { technical: true } );

		await Promise.all( [
			expect( page ).toMatchElement( '.amp-onboarding-wizard-panel h1', { text: 'Site Scan' } ),
			expect( page ).toMatchElement( '.site-scan__heading', { text: 'Please wait a minute' } ),
			testNextButton( { text: 'Next', disabled: true } ),
			testPreviousButton( { text: 'Previous' } ),
			testSiteScanning( {
				statusElementClassName: 'site-scan__status',
				isAmpFirst: true,
			} ),
		] );

		await expect( page ).toMatchElement( '.site-scan__heading', { text: 'Scan complete', timeout: 10000 } );
		await expect( page ).toMatchElement( '.site-scan__section p', { text: /Site scan found no issues/ } );

		await testNextButton( { text: 'Next' } );
		await testPreviousButton( { text: 'Previous' } );
	} );

	it( 'should list out plugin and theme issues after the scan', async () => {
		await activateTheme( 'hestia' );
		await activatePlugin( 'autoptimize' );

		await moveToSiteScanScreen( { technical: true } );

		await testSiteScanning( {
			statusElementClassName: 'site-scan__status',
			isAmpFirst: true,
		} );

		await expect( page ).toMatchElement( '.site-scan__heading', { text: 'Scan complete', timeout: 10000 } );
		await expect( page ).toMatchElement( '.site-scan__section p', { text: /Site scan found issues/ } );

		await expect( page ).toMatchElement( '.site-scan-results--themes' );
		await expect( page ).toMatchElement( '.site-scan-results--plugins' );

		const totalIssuesCount = await page.$$eval( '.site-scan-results__source', ( sources ) => sources.length );
		expect( totalIssuesCount ).toBe( 2 );

		await expect( page ).toMatchElement( '.site-scan-results--themes .site-scan-results__source-name', { text: /Hestia/ } );
		await expect( page ).toMatchElement( '.site-scan-results--plugins .site-scan-results__source-name', { text: /Autoptimize/ } );

		await testNextButton( { text: 'Next' } );
		await testPreviousButton( { text: 'Previous' } );

		await deactivatePlugin( 'autoptimize' );
		await activateTheme( 'twentytwenty' );
	} );
} );
