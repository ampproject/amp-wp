/**
 * WordPress dependencies
 */
import { activateTheme } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import {
	moveToTemplateModeScreen,
	clickMode,
	testNextButton,
	testPreviousButton,
} from '../../utils/onboarding-wizard-utils';

describe( 'Template mode', () => {
	beforeEach( async () => {
		await moveToTemplateModeScreen( { technical: true } );
	} );

	it( 'should show main page elements with nothing selected', async () => {
		await page.waitForSelector( 'input[type="radio"]' );

		await expect( 'input[type="radio"]' ).countToBe( 3 );

		await expect( page ).not.toMatchElement( 'input[type="radio"]:checked' );

		await testNextButton( { text: 'Next', disabled: true } );
		await testPreviousButton( { text: 'Previous' } );
	} );

	it( 'should allow options to be selected', async () => {
		await clickMode( 'standard' );
		await expect( page ).toMatchElement( '.selectable--selected h2', { text: 'Standard' } );

		await clickMode( 'transitional' );
		await expect( page ).toMatchElement( '.selectable--selected h2', { text: 'Transitional' } );

		await clickMode( 'reader' );
		await expect( page ).toMatchElement( '.selectable--selected h2', { text: 'Reader' } );

		await testNextButton( { text: 'Next' } );
	} );
} );

describe( 'Template mode recommendations with reader theme active', () => {
	it( 'makes correct recommendations when user is not technical and the current theme is a reader theme', async () => {
		await moveToTemplateModeScreen( { technical: false } );

		// The Reader option should be collapsed.
		await expect( page ).toMatchElement( '#template-mode-reader-container .components-panel__body-title button[aria-expanded="false"]' );

		// The Transitional and Standard modes should be expanded and should contain a "Recommended" string.
		await expect( page ).toMatchElement( '#template-mode-transitional-container .components-panel__body-title button[aria-expanded="true"]' );
		const transitionalCopy = await page.$eval( '#template-mode-transitional-container .amp-drawer__panel-body', ( el ) => el.innerText );
		expect( transitionalCopy ).toContain( 'Recommended' );

		await expect( page ).toMatchElement( '#template-mode-standard-container .components-panel__body-title button[aria-expanded="true"]' );
		const standardCopy = await page.$eval( '#template-mode-standard-container .amp-drawer__panel-body', ( el ) => el.innerText );
		expect( standardCopy ).toContain( 'Recommended' );
	} );

	it( 'makes correct recommendations when user is technical and the current theme is a reader theme', async () => {
		await moveToTemplateModeScreen( { technical: true } );

		// The Reader and Transitional options should be collapsed.
		await expect( page ).toMatchElement( '#template-mode-reader-container .components-panel__body-title button[aria-expanded="false"]' );
		await expect( page ).toMatchElement( '#template-mode-transitional-container .components-panel__body-title button[aria-expanded="false"]' );

		// The Standard mode should be expanded and should contain a success notice.
		await expect( page ).toMatchElement( '#template-mode-standard-container .components-panel__body-title button[aria-expanded="true"]' );
		await expect( page ).toMatchElement( '#template-mode-standard-container .amp-notice--success' );
	} );
} );

describe( 'Template mode recommendations with non-reader-theme active', () => {
	beforeAll( async () => {
		await activateTheme( 'hestia' );
	} );

	afterAll( async () => {
		await activateTheme( 'twentytwenty' );
	} );

	it( 'makes correct recommendations when user is not technical and the current theme is not a reader theme', async () => {
		await moveToTemplateModeScreen( { technical: false } );

		// The Reader mode should be recommended.
		await expect( page ).toMatchElement( '#template-mode-reader-container .components-panel__body-title button[aria-expanded="true"]' );
		await expect( page ).toMatchElement( '#template-mode-reader-container .amp-notice--success' );

		// The Standard and Transitional options should be collapsed.
		await expect( page ).toMatchElement( '#template-mode-standard-container .components-panel__body-title button[aria-expanded="false"]' );
		await expect( page ).toMatchElement( '#template-mode-transitional-container .components-panel__body-title button[aria-expanded="false"]' );
	} );

	it( 'makes correct recommendations when user is technical and the current theme is not a reader theme', async () => {
		await moveToTemplateModeScreen( { technical: true } );

		// The Reader mode should be recommended.
		await expect( page ).toMatchElement( '#template-mode-reader-container .components-panel__body-title button[aria-expanded="true"]' );
		await expect( page ).toMatchElement( '#template-mode-reader-container .amp-notice--success' );

		// Transitional should be recommended.
		await expect( page ).toMatchElement( '#template-mode-transitional-container .components-panel__body-title button[aria-expanded="true"]' );
		await expect( page ).toMatchElement( '#template-mode-transitional-container .amp-notice--success' );

		// The Standard option should not be recommended.
		await expect( page ).toMatchElement( '#template-mode-standard-container .components-panel__body-title button[aria-expanded="false"]' );
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
