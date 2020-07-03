/**
 * Internal dependencies
 */
import { moveToReaderThemesScreen, selectReaderTheme, testNextButton, testPreviousButton } from '../../utils/onboarding-wizard-utils';

describe( 'Reader themes', () => {
	beforeEach( async () => {
		await moveToReaderThemesScreen( { technical: true } );
	} );

	it( 'main components exist with no selection', async () => {
		const itemCount = await page.$$eval( '.theme-card', ( els ) => els.length );
		expect( itemCount ).toBe( 10 );

		await expect( page ).not.toMatchElement( 'input[type="radio"]:checked' );
		testNextButton( { text: 'Next', disabled: true } );
		testPreviousButton( { text: 'Previous' } );
	} );

	it( 'should allow different themes to be selected', async () => {
		await selectReaderTheme( 'legacy' );
		await expect( page ).toMatchElement( '.selectable--selected h2', { text: 'AMP Legacy' } );

		await selectReaderTheme( 'twentynineteen' );
		await expect( page ).toMatchElement( '.selectable--selected h2', { text: 'Twenty Nineteen' } );

		await selectReaderTheme( 'twentysixteen' );
		await expect( page ).toMatchElement( '.selectable--selected h2', { text: 'Twenty Sixteen' } );

		testNextButton( { text: 'Next' } );
	} );
} );

