
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
		expect( page ).toMatchElement( 'p', { text: /standard mode/i } );
		expect( page ).toMatchElement( '.phone iframe' );
		expect( page ).toMatchElement( 'a', { text: 'Visit' } );
	} );

	it( 'renders transitional mode done screen', async () => {
		await moveToDoneScreen( { mode: 'transitional' } );

		testCloseButton( { exists: false } );

		expect( page ).toMatchElement( 'h1', { text: 'Congratulations!' } );
		expect( page ).toMatchElement( 'p', { text: /transitional mode/i } );
		expect( page ).toMatchElement( '.phone iframe' );
		expect( page ).toMatchElement( 'a', { text: 'Visit' } );
	} );

	it( 'renders reader mode done screen', async () => {
		await moveToDoneScreen( { mode: 'reader' } );

		testCloseButton( { exists: false } );

		expect( page ).toMatchElement( 'h1', { text: 'Congratulations!' } );
		expect( page ).toMatchElement( '.phone iframe' );
		expect( page ).toMatchElement( 'p', { text: /reader mode/i } );
		expect( page ).toMatchElement( 'a', { text: 'Browse' } );
		expect( page ).toMatchElement( 'a', { text: 'Visit' } );
	} );
} );
