
/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

describe( 'AMP wizard: technical background', () => {
	beforeEach( async () => {
		await visitAdminPage( 'admin.php', 'page=amp-setup&amp-setup-screen=technical-background' );
	} );

	it( 'should show two options', async () => {
		await page.waitForSelector( 'input[type="radio"]' );

		const itemCount = await page.$$eval( 'input[type="radio"]', ( els ) => els.length );

		expect( itemCount ).toBe( 2 );
	} );

	it( 'should allow options to be selected', async () => {
		await page.waitForSelector( 'input[type="radio"]' );

		let titleText;

		await page.$eval( '[for="technical-background-enable"]', ( el ) => el.click() );
		titleText = await page.$eval( '.selectable--selected h2', ( el ) => el.innerText );
		expect( titleText ).toBe( 'Developer or technically savvy' );

		await page.$eval( '[for="technical-background-disable"]', ( el ) => el.click() );
		titleText = await page.$eval( '.selectable--selected h2', ( el ) => el.innerText );
		expect( titleText ).toBe( 'Non-technically savvy or wanting a simpler setup' );
	} );
} );
