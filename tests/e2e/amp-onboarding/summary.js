/* eslint-disable jest/no-export */
/* eslint-disable jest/require-top-level-describe */

/**
 * Internal dependencies
 */
import { moveToSummaryScreen } from './utils';

export const summary = () => {
	test( 'renders standard mode summary', async () => {
		await moveToSummaryScreen( { mode: 'standard' } );

		expect( page ).toMatchElement( 'h2', { text: 'Standard' } );
		expect( page ).toMatchElement( '.desktop img' );
		expect( page ).not.toMatchElement( '.phone img' );
		expect( page ).not.toMatchElement( '.components-form-toggle' );
	} );

	test( 'renders transitional mode summary', async () => {
		await moveToSummaryScreen( { mode: 'transitional' } );

		expect( page ).toMatchElement( 'h2', { text: 'Transitional' } );
		expect( page ).toMatchElement( '.desktop img' );
		expect( page ).not.toMatchElement( '.phone img' );
		expect( page ).toMatchElement( '.components-form-toggle.is-checked' );
	} );

	test( 'renders reader mode summary', async () => {
		await moveToSummaryScreen( { mode: 'reader' } );

		expect( page ).toMatchElement( 'h2', { text: 'Reader' } );
		expect( page ).toMatchElement( '.desktop img' );
		expect( page ).toMatchElement( '.phone img' );
		expect( page ).toMatchElement( '.components-form-toggle.is-checked' );
	} );
};

/* eslint-enable jest/require-top-level-describe */
/* eslint-enable jest/no-export */
