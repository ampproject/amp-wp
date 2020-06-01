
/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

describe( 'AMP Setup Screen', () => {
	beforeEach( async () => {
		await visitAdminPage( 'admin.php', 'page=amp-setup&amp-new-onboarding=1&amp-setup-screen=reader-themes' );
	} );

	it( 'should have themes', async () => {
		await page.waitForSelector( '.amp-wp-theme-card' );

		const itemCount = await page.$$eval( '.amp-wp-theme-card', ( els ) => els.length );

		expect( itemCount ).toBe( 10 );

		await page.click( 'label[for="3"]' );
		let titleText = await page.$eval( '.amp-wp-theme-card--selected h2', ( el ) => el.innerText );
		expect( titleText ).toBe( 'Twenty Sixteen' );

		await page.click( 'label[for="0"]' );
		titleText = await page.$eval( '.amp-wp-theme-card--selected h2', ( el ) => el.innerText );
		expect( titleText ).toBe( 'Twenty Twenty' );
	} );
} );
