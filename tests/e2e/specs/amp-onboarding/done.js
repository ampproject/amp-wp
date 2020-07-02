
/**
 * Internal dependencies
 */
import { moveToDoneScreen, testCloseButton, cleanUpWizard } from '../../utils/onboarding-wizard-utils';

describe( 'Done', () => {
	afterEach( async () => {
		await cleanUpWizard();
	} );

	it( 'renders standard mode done screen', async () => {
		await moveToDoneScreen( { mode: 'standard' } );

		testCloseButton( { exists: false } );

		expect( page ).toMatchElement( 'h1', { text: 'Your site is ready' } );
		expect( page ).toMatchElement( '.phone iframe' );
	} );

	it( 'renders transitional mode done screen', async () => {
		await moveToDoneScreen( { mode: 'transitional' } );

		testCloseButton( { exists: false } );

		expect( page ).toMatchElement( 'h1', { text: 'Congratulations!' } );
		expect( page ).toMatchElement( '.phone iframe' );
	} );

	it( 'renders reader mode done screen', async () => {
		await moveToDoneScreen( { mode: 'transitional' } );

		testCloseButton( { exists: false } );

		expect( page ).toMatchElement( 'h1', { text: 'Congratulations!' } );
		expect( page ).toMatchElement( '.phone iframe' );
	} );
} );
