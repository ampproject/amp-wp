/**
 * Internal dependencies
 */
import { moveToTechnicalScreen, testTitle, testNextButton, testPreviousButton } from '../../utils/onboarding-wizard-utils';

describe( 'Technical background', () => {
	it( 'main components exist', async () => {
		await moveToTechnicalScreen();

		await testTitle( { text: 'Are you technical?' } );

		await expect( page ).toMatchElement( 'p', { text: /^In order to/ } );

		testNextButton( { text: 'Next', disabled: true } );
		testPreviousButton( { text: 'Previous' } );
	} );

	it( 'should show two options, none checked', async () => {
		await page.waitForSelector( 'input[type="radio"]' );

		await expect( 'input[type="radio"]' ).countToBe( 2 );

		await expect( page ).not.toMatchElement( 'input[type="radio"]:checked' );
	} );

	it( 'should allow options to be selected, then enable next button', async () => {
		await page.waitForSelector( '#technical-background-enable' );

		await expect( page ).toClick( '#technical-background-enable' );
		await expect( page ).toMatchElement( '.selectable--selected h2', { text: 'Developer or technically savvy' } );

		await expect( page ).toClick( 'label', { text: /Non-technically savvy/ } );
		await expect( page ).toMatchElement( '.selectable--selected h2', { text: 'Non-technically savvy or wanting a simpler setup' } );

		testNextButton( { text: 'Next', disabled: false } );
	} );
} );
