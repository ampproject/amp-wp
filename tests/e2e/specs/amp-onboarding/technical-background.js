
/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

describe( 'AMP wizard: technical background', () => {
	beforeEach( async () => {
		await visitAdminPage( 'admin.php', 'page=amp-setup&amp-new-onboarding=1&amp-setup-screen=technical-background' );
	} );

	it( 'should show two options', async () => {
		await page.waitForSelector( 'input[type="radio"]' );

		const itemCount = await page.$$eval( 'input[type="radio"]', ( els ) => els.length );

		expect( itemCount ).toBe( 2 );
	} );

	it( 'should allow options to be selected', async () => {
		await page.waitForSelector( 'input[type="radio"]' );

		let titleText;

		await page.$eval( '[for="amp-technical-background-disable"]', ( el ) => el.click() );
		titleText = await page.$eval( '.amp-technical-background-option-container--active h2', ( el ) => el.innerText );
		expect( titleText ).toBe( 'Developer or Technically Savvy' );

		await page.$eval( '[for="amp-technical-background-enable"]', ( el ) => el.click() );
		titleText = await page.$eval( '.amp-technical-background-option-container--active h2', ( el ) => el.innerText );
		expect( titleText ).toBe( 'Non-technically Savvy or Wanting a simpler setup' );
	} );
} );
