
/**
 * Internal dependencies
 */
import { testCloseButton, cleanUpSettings, moveToDoneScreen } from '../../utils/onboarding-wizard-utils';

describe( 'Review', () => {
	afterEach( async () => {
		await cleanUpSettings();
	} );

	it( 'renders standard mode review screen', async () => {
		await moveToDoneScreen( { mode: 'standard' } );

		testCloseButton( { exists: false } );

		await expect( page ).toMatchElement( 'h1', { text: 'Your site is live!' } );
		await expect( page ).toMatchElement( 'p', { text: /standard mode/i } );
		await expect( page ).toMatchElement( '.phone iframe' );
	} );

	it( 'renders transitional mode review screen', async () => {
		await moveToDoneScreen( { mode: 'transitional' } );

		testCloseButton( { exists: false } );

		await expect( page ).toMatchElement( 'h1', { text: 'Your site is live!' } );
		await expect( page ).toMatchElement( 'p', { text: /transitional mode/i } );
		await expect( page ).toMatchElement( '.phone iframe' );
	} );

	it( 'renders reader mode review screen', async () => {
		await moveToDoneScreen( { mode: 'reader' } );

		testCloseButton( { exists: true } );

		await expect( page ).toMatchElement( 'h1', { text: 'Your site is live!' } );
		await expect( page ).toMatchElement( 'p', { text: /reader mode/i } );
		await expect( page ).toMatchElement( '.phone iframe' );
	} );
} );
