/**
 * Internal dependencies
 */
import { moveToSummaryScreen, testNextButton, testPreviousButton } from '../../utils/onboarding-wizard-utils';

const sharedTests = () => {
	expect( page ).toMatchElement( '.desktop img' );
	testNextButton( { text: 'Next' } );
	testPreviousButton( { text: 'Previous' } );
};

describe( 'Summary', () => {
	it( 'renders standard mode summary', async () => {
		await moveToSummaryScreen( { mode: 'standard' } );

		expect( page ).toMatchElement( 'h2', { text: 'Standard' } );
		expect( page ).not.toMatchElement( '.phone img' );
		expect( page ).not.toMatchElement( '.components-form-toggle' );

		sharedTests();
	} );

	it( 'renders transitional mode summary', async () => {
		await moveToSummaryScreen( { mode: 'transitional' } );

		expect( page ).toMatchElement( 'h2', { text: 'Transitional' } );
		expect( page ).not.toMatchElement( '.phone img' );
		expect( page ).toMatchElement( '.components-form-toggle.is-checked' );

		sharedTests();
	} );

	it( 'renders reader mode summary', async () => {
		await moveToSummaryScreen( { mode: 'reader' } );

		expect( page ).toMatchElement( 'h2', { text: 'Reader' } );
		expect( page ).toMatchElement( '.phone img' );
		expect( page ).toMatchElement( '.components-form-toggle.is-checked' );

		sharedTests();
	} );
} );
