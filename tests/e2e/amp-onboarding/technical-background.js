/* eslint-disable jest/no-export */
/* eslint-disable jest/require-top-level-describe */

/**
 * Internal dependencies
 */
import { moveToTechnicalScreen, testTitle, testNextButton, testPreviousButton, testElementCount } from './utils';

export const technicalBackground = () => {
	beforeEach( async () => {
		await moveToTechnicalScreen();
	} );

	test( 'main components exist', async () => {
		await testTitle( { text: 'Are you technical?' } );

		expect( page ).toMatchElement( 'p', { text: /^In order to/ } );

		testNextButton( { text: 'Next' } );
		testPreviousButton( { text: 'Previous' } );
	} );

	test( 'should show two options, none checked', async () => {
		await page.waitForSelector( 'input[type="radio"]' );

		await testElementCount( 'input[type="radio"]', 2 );

		expect( page ).not.toMatchElement( 'input[type="radio"][checked]' );
	} );

	test( 'should allow options to be selected, then enable next button', async () => {
		await page.waitForSelector( 'input[type="radio"]' );

		await expect( page ).toClick( 'label', { text: /Developer or technically savvy/ } );
		expect( page ).toMatchElement( '.selectable--selected h2', { text: 'Developer or technically savvy' } );

		await expect( page ).toClick( 'label', { text: /Non-technically savvy/ } );
		expect( page ).toMatchElement( '.selectable--selected h2', { text: 'Non-technically savvy or wanting a simpler setup' } );

		testNextButton( { text: 'Next', disabled: false } );
	} );
};

/* eslint-enable jest/require-top-level-describe */
/* eslint-enable jest/no-export */
