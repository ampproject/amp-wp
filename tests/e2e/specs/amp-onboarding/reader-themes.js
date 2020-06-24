
/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

describe( 'AMP wizard: reader themes', () => {
	beforeEach( async () => {
		await visitAdminPage( 'admin.php', 'page=amp-setup&amp-new-onboarding=1&amp-setup-screen=template-modes' );
		await page.waitForSelector( '#reader-mode' );
		await page.$eval( '#reader-mode', ( el ) => el.click() );
		await page.waitForSelector( '.amp-setup-nav__prev-next .is-primary' );
		await page.$eval( '.amp-setup-nav__prev-next .is-primary', ( el ) => el.click() );
	} );

	it( 'should have themes', async () => {
		await page.waitForSelector( '.theme-card' );

		const itemCount = await page.$$eval( '.theme-card', ( els ) => els.length );

		expect( itemCount ).toBe( 10 );
	} );

	it( 'should allow different themes to be selected', async () => {
		await page.waitForSelector( '.theme-card' );

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
