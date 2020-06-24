
/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

describe( 'AMP wizard: template-mode', () => {
	beforeEach( async () => {
		// Technical background must be selected.
		await visitAdminPage( 'admin.php', 'page=amp-setup&amp-new-onboarding=1&amp-setup-screen=technical-background' );

		await page.waitForSelector( 'input[type="radio"]' );
		await page.$eval( 'input[type="radio"]', ( el ) => el.click() );

		await page.waitForSelector( '.amp-setup-nav__prev-next .components-button.is-primary' );
		await page.$eval( '.amp-setup-nav__prev-next .components-button.is-primary', ( el ) => el.click() );
	} );

	it( 'should show two options', async () => {
		await page.waitForSelector( 'input[type="radio"]' );

		const itemCount = await page.$$eval( 'input[type="radio"]', ( els ) => els.length );

		expect( itemCount ).toBe( 3 );
	} );

	it( 'should allow options to be selected', async () => {
		await page.waitForSelector( 'input[type="radio"]' );

		let titleText;

		await page.$eval( '[for="standard-mode"]', ( el ) => el.click() );
		titleText = await page.$eval( '.selectable--selected h2', ( el ) => el.innerText );
		expect( titleText ).toBe( 'Standard' );

		await page.$eval( '[for="transitional-mode"]', ( el ) => el.click() );
		titleText = await page.$eval( '.selectable--selected h2', ( el ) => el.innerText );
		expect( titleText ).toBe( 'Transitional' );

		await page.$eval( '[for="reader-mode"]', ( el ) => el.click() );
		titleText = await page.$eval( '.selectable--selected h2', ( el ) => el.innerText );
		expect( titleText ).toBe( 'Reader' );
	} );
} );
