
/**
 * Internal dependencies
 */
import { completeWizard, testCloseButton, cleanUpWizard } from '../../utils/onboarding-wizard-utils';

describe( 'Done', () => {
	afterEach( async () => {
		await cleanUpWizard();
	} );

	it( 'renders standard mode done screen', async () => {
		await completeWizard( { mode: 'standard' } );

		testCloseButton( { exists: false } );

		await expect( page ).toMatchElement( 'h1', { text: 'Your site is ready' } );
		await expect( page ).toMatchElement( 'p', { text: /standard mode/i } );
		await expect( page ).toMatchElement( '.phone iframe' );
		await expect( page ).toMatchElement( 'a', { text: 'Visit' } );
	} );

	it( 'renders transitional mode done screen', async () => {
		await completeWizard( { mode: 'transitional' } );

		testCloseButton( { exists: false } );

		await expect( page ).toMatchElement( 'h1', { text: 'Congratulations!' } );
		await expect( page ).toMatchElement( 'p', { text: /transitional mode/i } );
		await expect( page ).toMatchElement( '.phone iframe' );
		await expect( page ).toMatchElement( 'a', { text: 'Visit' } );
	} );

	it( 'renders reader mode done screen', async () => {
		await completeWizard( { mode: 'reader' } );

		testCloseButton( { exists: false } );

		await expect( page ).toMatchElement( 'h1', { text: 'Congratulations!' } );
		await expect( page ).toMatchElement( '.phone iframe' );
		await expect( page ).toMatchElement( 'p', { text: /reader mode/i } );
		await expect( page ).toMatchElement( 'a', { text: 'Browse' } );
		await expect( page ).toMatchElement( 'a', { text: 'Customize' } );
	} );
} );
