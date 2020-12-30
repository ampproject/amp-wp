/**
 * WordPress dependencies
 */
import { activateTheme, deleteTheme, installTheme } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import {
	moveToTemplateModeScreen,
	clickMode,
	testNextButton,
	testPreviousButton,
	cleanUpSettings,
} from '../../utils/onboarding-wizard-utils';

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

describe( 'Template mode recommendations with reader theme active', () => {
	beforeEach( async () => {
		await activateTheme( 'twentytwenty' );
	} );

	it.each(
		[ 'technical', 'nontechnical' ],
	)( 'makes correct recommendations when user is not %s and the current theme is a reader theme', async ( technical ) => {
		await moveToTemplateModeScreen( { technical: technical === 'technical' } );

		await expect( page ).toMatchElement( '#template-mode-standard-container .amp-notice--info' );
		await expect( page ).toMatchElement( '#template-mode-transitional-container .amp-notice--success' );
		await expect( page ).toMatchElement( '#template-mode-reader-container .amp-notice--success' );
	} );
} );

describe( 'Stepper item modifications', () => {
	beforeEach( async () => {
		await moveToTemplateModeScreen( { technical: 'technical' } );
	} );

	it( 'adds the "Theme Selection" page when reader mode is selected', async () => {
		await clickMode( 'reader' );

		const itemCount = await page.$$eval( '.amp-stepper__item', ( els ) => els.length );
		expect( itemCount ).toBe( 6 );

		await expect( page ).toMatchElement( '.amp-stepper__item-title', { text: 'Theme Selection' } );
	} );

	it( 'removes the "Theme Selection" page when reader mode is not selected', async () => {
		await clickMode( 'transitional' );

		const itemCount = await page.$$eval( '.amp-stepper__item', ( els ) => els.length );
		expect( itemCount ).toBe( 5 );

		await expect( page ).not.toMatchElement( '.amp-stepper__item-title', { text: 'Theme Selection' } );
	} );
} );

describe( 'Template mode recommendations with non-reader-theme active', () => {
	beforeEach( async () => {
		await cleanUpSettings();
		await installTheme( 'hestia' );
		await activateTheme( 'hestia' );
	} );

	afterEach( async () => {
		await deleteTheme( 'hestia', 'twentytwenty' );
	} );

	it( 'makes correct recommendations when user is not technical and the current theme is not a reader theme', async () => {
		await moveToTemplateModeScreen( { technical: false } );

		await expect( page ).toMatchElement( '#template-mode-standard-container .amp-notice--info' );
		await expect( page ).toMatchElement( '#template-mode-transitional-container .amp-notice--info' );
		await expect( page ).toMatchElement( '#template-mode-reader-container .amp-notice--success' );
	} );
} );
