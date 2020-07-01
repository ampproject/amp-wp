
/**
 * Internal dependencies
 */
import { moveToTemplateModeScreen } from './utils';

describe( 'AMP wizard: template-mode', () => {
	beforeEach( async () => {
		await moveToTemplateModeScreen();
	} );

	it( 'should show two options, none selected', async () => {
		await page.waitForSelector( 'input[type="radio"]' );

		const itemCount = await page.$$eval( 'input[type="radio"]', ( els ) => els.length );

		expect( itemCount ).toBe( 3 );

		const checkedRadio = await page.$( 'input[type="radio"][checked]' );
		expect( checkedRadio ).toBeNull();
	} );

	it( 'should allow options to be selected', async () => {
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

describe( 'AMP wizard: template mode recommentations', () => {
	it( 'makes correct recommendations when user is techncial', async () => {
		await moveToTemplateModeScreen();

		const infoNoticeCount = await page.$$eval( '.amp-notice--info', ( els ) => els.length );

		expect( infoNoticeCount ).toBe( 3 );
	} );

	it( 'makes correct recommendations when user is not techncial', async () => {
		await moveToTemplateModeScreen( false );

		const infoNoticeCount = await page.$$eval( '.amp-notice--info', ( els ) => els.length );
		expect( infoNoticeCount ).toBe( 2 );

		const successNoticeCount = await page.$$eval( '.amp-notice--success', ( els ) => els.length );
		expect( successNoticeCount ).toBe( 1 );
	} );
} );
