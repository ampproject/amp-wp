/**
 * Internal dependencies
 */
import { moveToReaderThemesScreen, selectReaderTheme, testNextButton, testPreviousButton } from '../../utils/onboarding-wizard-utils';

describe( 'Reader themes', () => {
	beforeEach( async () => {
		await moveToReaderThemesScreen( { technical: true } );
	} );

	it( 'shows the correct active stepper item', async () => {
		const itemCount = await page.$$eval( '.amp-stepper__item', ( els ) => els.length );
		expect( itemCount ).toBe( 6 );

		await expect( page ).toMatchElement( '.amp-stepper__item--active', { text: 'Theme Selection' } );
	} );

	it( 'main components exist with no selection', async () => {
		const itemCount = await page.$$eval( '.theme-card', ( els ) => els.length );
		expect( itemCount ).toBe( 11 );

		await expect( page ).not.toMatchElement( 'input[type="radio"]:checked' );
		await testNextButton( { text: 'Next', disabled: true } );
		await testPreviousButton( { text: 'Previous' } );
	} );

	it( 'should allow different themes to be selected', async () => {
		await selectReaderTheme( 'legacy' );
		await expect( page ).toMatchElement( '.selectable--selected h4', { text: 'AMP Legacy' } );

		await selectReaderTheme( 'twentynineteen' );
		await expect( page ).toMatchElement( '.selectable--selected h4', { text: 'Twenty Nineteen' } );

		await selectReaderTheme( 'twentysixteen' );
		await expect( page ).toMatchElement( '.selectable--selected h4', { text: 'Twenty Sixteen' } );

		await testNextButton( { text: 'Next' } );
	} );
} );

