/**
 * Internal dependencies
 */
import { moveToTemplateModeScreen, clickMode, testNextButton, testPreviousButton } from '../../utils/onboarding-wizard-utils';
import { installTheme } from '../../utils/install-theme';
import { activateTheme } from '../../utils/activate-theme';

describe( 'Template mode', () => {
	beforeEach( async () => {
		await moveToTemplateModeScreen( { technical: true } );
	} );

	it( 'should show main page elements with nothing selected', async () => {
		await page.waitForSelector( 'input[type="radio"]' );

		await expect( 'input[type="radio"]' ).countToBe( 3 );

		await expect( page ).not.toMatchElement( 'input[type="radio"]:checked' );

		testNextButton( { text: 'Next', disabled: true } );
		testPreviousButton( { text: 'Previous' } );
	} );

	it( 'should allow options to be selected', async () => {
		await clickMode( 'standard' );
		await expect( page ).toMatchElement( '.selectable--selected h2', { text: 'Standard' } );

		await clickMode( 'transitional' );
		await expect( page ).toMatchElement( '.selectable--selected h2', { text: 'Transitional' } );

		await clickMode( 'reader' );
		await expect( page ).toMatchElement( '.selectable--selected h2', { text: 'Reader' } );

		testNextButton( { text: 'Next' } );
	} );
} );

describe( 'Template mode recommendations', () => {
	beforeEach( async () => {
		await activateTheme( 'twentytwenty' );
	} );

	it( 'makes correct recommendations when user is technical', async () => {
		await moveToTemplateModeScreen( { technical: true } );

		await expect( '.amp-notice--info' ).countToBe( 3 );
	} );

	it( 'makes correct recommendations when user is not technnical and the current theme is a reader theme', async () => {
		await moveToTemplateModeScreen( { technical: false } );

		await expect( '.amp-notice--info' ).countToBe( 1 );
		await expect( '.amp-notice--success' ).countToBe( 2 );
	} );

	it( 'makes correct recommendations when user is not technical and the current theme is not a reader theme', async () => {
		await installTheme( 'astra' );
		await activateTheme( 'astra' );

		await moveToTemplateModeScreen( { technical: false } );

		await expect( '.amp-notice--info' ).countToBe( 2 );
		await expect( '.amp-notice--success' ).countToBe( 1 );

		await activateTheme( 'twentytwenty' );
	} );
} );
