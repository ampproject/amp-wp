
/**
 * Internal dependencies
 */
import { testCloseButton, cleanUpSettings, moveToDoneScreen } from '../../utils/onboarding-wizard-utils';

describe( 'Done', () => {
	afterEach( async () => {
		await cleanUpSettings();
	} );

	it( 'renders standard mode done screen', async () => {
		await moveToDoneScreen( { mode: 'standard' } );

		testCloseButton( { exists: false } );

		await expect( page ).toMatchElement( 'h1', { text: 'Your site is ready' } );
		await expect( page ).toMatchElement( 'p', { text: /standard mode/i } );
		await expect( page ).toMatchElement( '.phone iframe' );
		await expect( page ).toMatchElement( 'a', { text: 'Browse AMP' } );
	} );

	it( 'renders transitional mode done screen', async () => {
		await moveToDoneScreen( { mode: 'transitional' } );

		testCloseButton( { exists: false } );

		await expect( page ).toMatchElement( 'h1', { text: 'Congratulations!' } );
		await expect( page ).toMatchElement( 'p', { text: /transitional mode/i } );
		await expect( page ).toMatchElement( '.phone iframe' );
		await expect( page ).toMatchElement( 'a', { text: 'Browse AMP' } );
	} );

	it( 'renders reader mode done screen', async () => {
		await moveToDoneScreen( { mode: 'reader' } );

		testCloseButton( { exists: true } );

		await expect( page ).toMatchElement( 'h1', { text: 'Congratulations!' } );
		await expect( page ).toMatchElement( '.phone iframe' );
		await expect( page ).toMatchElement( 'p', { text: /reader mode/i } );
		await expect( page ).toMatchElement( 'a', { text: 'Browse AMP' } );
		await expect( page ).toMatchElement( 'a', { text: 'Customize' } );
	} );
} );
