/* eslint-disable jest/no-export */
/* eslint-disable jest/require-top-level-describe */

/**
 * Internal dependencies
 */
import { moveToTechnicalScreen } from './utils';

export const technicalBackground = () => {
	beforeEach( async () => {
		await moveToTechnicalScreen();
	} );

	test( 'should show two options, none checked', async () => {
		await page.waitForSelector( 'input[type="radio"]' );

		const itemCount = await page.$$eval( 'input[type="radio"]', ( els ) => els.length );

		expect( itemCount ).toBe( 2 );

		const checkedRadio = await page.$( 'input[type="radio"][checked]' );
		expect( checkedRadio ).toBeNull();
	} );

	test( 'should allow options to be selected', async () => {
		await page.waitForSelector( 'input[type="radio"]' );

		let titleText;

		await page.$eval( '[for="technical-background-enable"]', ( el ) => el.click() );
		titleText = await page.$eval( '.selectable--selected h2', ( el ) => el.innerText );
		expect( titleText ).toBe( 'Developer or technically savvy' );

		await page.$eval( '[for="technical-background-disable"]', ( el ) => el.click() );
		titleText = await page.$eval( '.selectable--selected h2', ( el ) => el.innerText );
		expect( titleText ).toBe( 'Non-technically savvy or wanting a simpler setup' );
	} );
};

/* eslint-enable jest/require-top-level-describe */
/* eslint-enable jest/no-export */
