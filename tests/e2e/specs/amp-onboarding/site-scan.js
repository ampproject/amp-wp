/**
 * Internal dependencies
 */
import {
	moveToSiteScanScreen,
	testNextButton,
	testPreviousButton,
} from '../../utils/onboarding-wizard-utils';
import { testSiteScanning } from '../../utils/site-scan-utils';

describe( 'Site Scan', () => {
	beforeEach( async () => {
		await moveToSiteScanScreen( { technical: true } );
	} );

	it( 'should start a site scan immediately', async () => {
		await page.waitForSelector( '.amp-onboarding-wizard-panel h1' );

		const screenHeading = await page.$eval( '.amp-onboarding-wizard-panel h1', ( el ) => el.innerText );
		expect( screenHeading ).toContain( 'Site Scan' );

		const scanInProgressHandle = await page.waitForXPath( `//p[contains(text(), 'Please wait a minute')]` );
		expect( scanInProgressHandle ).not.toBeNull();

		testNextButton( { text: 'Next', disabled: true } );
		testPreviousButton( { text: 'Previous' } );

		await testSiteScanning( {
			statusElementClassName: 'site-scan__status',
			isAmpFirst: true,
		} );

		const scanCompleteHandle = await page.waitForXPath( `//p[@class='site-scan__heading'][contains(text(), 'Scan complete')]` );
		expect( scanCompleteHandle ).not.toBeNull();

		testNextButton( { text: 'Next' } );
		testPreviousButton( { text: 'Previous' } );
	} );
} );
