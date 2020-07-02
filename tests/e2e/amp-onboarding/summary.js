/* eslint-disable jest/no-export */
/* eslint-disable jest/require-top-level-describe */

/**
 * Internal dependencies
 */
import { moveToSummaryScreen, testNextButton, testPreviousButton } from './utils';

const sharedTests = () => {
	expect( page ).toMatchElement( '.desktop img' );
	testNextButton( { text: 'Next' } );
	testPreviousButton( { text: 'Previous' } );
};

export const summary = () => {
	test( 'renders standard mode summary', async () => {
		await moveToSummaryScreen( { mode: 'standard' } );

		expect( page ).toMatchElement( 'h2', { text: 'Standard' } );
		expect( page ).not.toMatchElement( '.phone img' );
		expect( page ).not.toMatchElement( '.components-form-toggle' );

		sharedTests();
	} );

	test( 'renders transitional mode summary', async () => {
		await moveToSummaryScreen( { mode: 'transitional' } );

		expect( page ).toMatchElement( 'h2', { text: 'Transitional' } );
		expect( page ).not.toMatchElement( '.phone img' );
		expect( page ).toMatchElement( '.components-form-toggle.is-checked' );

		sharedTests();
	} );

	test( 'renders reader mode summary', async () => {
		await moveToSummaryScreen( { mode: 'reader' } );

		expect( page ).toMatchElement( 'h2', { text: 'Reader' } );
		expect( page ).toMatchElement( '.phone img' );
		expect( page ).toMatchElement( '.components-form-toggle.is-checked' );

		sharedTests();
	} );
};

/* eslint-enable jest/require-top-level-describe */
/* eslint-enable jest/no-export */
