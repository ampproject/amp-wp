/* eslint-disable jest/no-export */
/* eslint-disable jest/require-top-level-describe */

/**
 * Internal dependencies
 */
import { moveToReaderThemesScreen, selectReaderTheme } from './utils';

export const readerThemes = () => {
	beforeEach( async () => {
		await moveToReaderThemesScreen( { technical: true } );
	} );

	test( 'should have themes, none selected', async () => {
		await page.waitForSelector( '.theme-card' );

		const itemCount = await page.$$eval( '.theme-card', ( els ) => els.length );

		expect( itemCount ).toBe( 9 );

		const checkedRadio = await page.$( 'input[type="radio"][checked]' );
		expect( checkedRadio ).toBeNull();
	} );

	test( 'should allow different themes to be selected', async () => {
		// Twenty twenty shouldn't show because it's the active theme.
		const twentytwenty = await page.$( '[for="theme-card__twentytwenty"]' );
		expect( twentytwenty ).toBeNull();

		await selectReaderTheme( 'legacy' );
		let titleText = await page.$eval( '.selectable--selected h2', ( el ) => el.innerText );
		expect( titleText ).toBe( 'AMP Legacy' );

		await selectReaderTheme( 'twentynineteen' );
		titleText = await page.$eval( '.selectable--selected h2', ( el ) => el.innerText );
		expect( titleText ).toBe( 'Twenty Nineteen' );

		await selectReaderTheme( 'twentysixteen' );
		titleText = await page.$eval( '.selectable--selected h2', ( el ) => el.innerText );
		expect( titleText ).toBe( 'Twenty Sixteen' );
	} );
};

/* eslint-enable jest/require-top-level-describe */
/* eslint-enable jest/no-export */
