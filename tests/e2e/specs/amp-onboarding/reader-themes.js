
/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

describe( 'AMP wizard: reader themes', () => {
	beforeEach( async () => {
		await visitAdminPage( 'admin.php', 'page=amp-setup&amp-new-onboarding=1&amp-setup-screen=reader-themes' );
	} );

	it( 'should have themes', async () => {
		await page.waitForSelector( '.amp-wp-theme-card' );

		const itemCount = await page.$$eval( '.amp-wp-theme-card', ( els ) => els.length );

		expect( itemCount ).toBe( 10 );
	} );

	it( 'should allow different themes to be selected', async () => {
		await page.waitForSelector( '.amp-wp-theme-card' );

		let titleText = await page.$eval( '.selectable--selected h2', ( el ) => el.innerText );
		expect( titleText ).toBe( 'AMP Classic' );

		await page.$eval( '[for="theme-card__twentytwenty"]', ( el ) => el.click() );
		titleText = await page.$eval( '.selectable--selected h2', ( el ) => el.innerText );
		expect( titleText ).toBe( 'Twenty Twenty' );

		await page.$eval( '[for="theme-card__twentysixteen"]', ( el ) => el.click() );
		titleText = await page.$eval( '.selectable--selected h2', ( el ) => el.innerText );
		expect( titleText ).toBe( 'Twenty Sixteen' );
	} );
} );
