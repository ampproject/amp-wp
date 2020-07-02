/* eslint-disable jest/no-export */
/* eslint-disable jest/require-top-level-describe */

/**
 * Internal dependencies
 */
import { moveToTemplateModeScreen, clickMode } from './utils';

export const templateMode = () => {
	beforeEach( async () => {
		await moveToTemplateModeScreen( { technical: true } );
	} );

	test( 'should show main page elements with nothing selected', async () => {
		await page.waitForSelector( 'input[type="radio"]' );

		const itemCount = await page.$$eval( 'input[type="radio"]', ( els ) => els.length );
		expect( itemCount ).toBe( 3 );

		expect( page ).not.toMatchElement( 'input[type="radio"][checked]' );
	} );

	test( 'should allow options to be selected', async () => {
		await clickMode( 'standard' );
		expect( page ).toMatchElement( '.selectable--selected h2', { text: 'Standard' } );

		await clickMode( 'transitional' );
		expect( page ).toMatchElement( '.selectable--selected h2', { text: 'Transitional' } );

		await clickMode( 'reader' );
		expect( page ).toMatchElement( '.selectable--selected h2', { text: 'Reader' } );
	} );
};

export const templateModeRecommendations = () => {
	test( 'makes correct recommendations when user is techncial', async () => {
		await moveToTemplateModeScreen( { technical: true } );

		const infoNoticeCount = await page.$$eval( '.amp-notice--info', ( els ) => els.length );

		expect( infoNoticeCount ).toBe( 3 );
	} );

	test( 'makes correct recommendations when user is not techncial', async () => {
		await moveToTemplateModeScreen( { technical: false } );

		const infoNoticeCount = await page.$$eval( '.amp-notice--info', ( els ) => els.length );
		expect( infoNoticeCount ).toBe( 2 );

		const successNoticeCount = await page.$$eval( '.amp-notice--success', ( els ) => els.length );
		expect( successNoticeCount ).toBe( 1 );
	} );
};

/* eslint-enable jest/require-top-level-describe */
/* eslint-enable jest/no-export */
