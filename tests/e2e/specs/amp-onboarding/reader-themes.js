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
		expect( itemCount ).toBe( 9 );

		expect( page ).not.toMatchElement( 'input[type="radio"]:checked' );
		testNextButton( { text: 'Next', disabled: true } );
		testPreviousButton( { text: 'Previous' } );
	} );

	it( 'should allow different themes to be selected', async () => {
		// Twenty twenty shouldn't show because it's the active theme.
		expect( page ).not.toMatchElement( '[for="theme-card__twentytwenty"]' );

		await selectReaderTheme( 'legacy' );
		expect( page ).toMatchElement( '.selectable--selected h2', { text: 'AMP Legacy' } );

		await selectReaderTheme( 'twentynineteen' );
		expect( page ).toMatchElement( '.selectable--selected h2', { text: 'Twenty Nineteen' } );

		await selectReaderTheme( 'twentysixteen' );
		expect( page ).toMatchElement( '.selectable--selected h2', { text: 'Twenty Sixteen' } );

		testNextButton( { text: 'Next' } );
	} );
} );
