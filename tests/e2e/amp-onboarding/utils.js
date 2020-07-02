/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils/build/visit-admin-page';

export const NEXT_BUTTON_SELECTOR = '.amp-setup-nav__prev-next button.is-primary';
export const PREV_BUTTON_SELECTOR = '.amp-setup-nav__prev-next button:not(.is-primary)';

export async function clickNextButton() {
	await page.waitForSelector( `${ NEXT_BUTTON_SELECTOR }:not([disabled])` );
	await expect( page ).toClick( 'button', { text: 'Next' } );
}

export async function clickPrevButton() {
	await page.waitForSelector( `${ PREV_BUTTON_SELECTOR }:not([disabled])` );
	await expect( page ).toClick( 'button', { text: 'Previous' } );
}

export async function moveToTechnicalScreen() {
	await visitAdminPage( 'admin.php', 'page=amp-setup' );
	await clickNextButton();
	await page.waitForSelector( '.technical-background' );
}

export async function moveToTemplateModeScreen( { technical } ) {
	await moveToTechnicalScreen();

	const radioSelector = technical ? '#technical-background-enable' : '#technical-background-disable';

	await page.waitForSelector( radioSelector );
	await page.$eval( radioSelector, ( el ) => el.click() );

	await clickNextButton();
	await page.waitForSelector( '.template-mode-selection' );
}

export async function clickMode( mode ) {
	await page.$eval( `[for="${ mode }-mode"]`, ( el ) => el.click() );
}

export async function moveToReaderThemesScreen( { technical } ) {
	await moveToTemplateModeScreen( { technical } );
	await clickMode( 'reader' );
	await clickNextButton();
	await page.waitForSelector( '.theme-card' );
}

export async function selectReaderTheme( theme = 'legacy' ) {
	const selector = `[for="theme-card__${ theme }"]`;

	await page.waitForSelector( selector );
	await page.$eval( selector, ( el ) => el.click() );
}

export async function moveToSummaryScreen( { technical = true, mode, readerTheme = 'legacy' } ) {
	await moveToTemplateModeScreen( { technical } );
	await clickMode( mode );

	if ( mode === 'reader' ) {
		await clickNextButton();
		await selectReaderTheme( readerTheme );
	}

	await clickNextButton();
	await page.waitForSelector( '.summary' );
}

export async function moveToDoneScreen( { technical = true, mode, readerTheme = 'legacy' } ) {
	await moveToSummaryScreen( { technical, mode, readerTheme } );

	await clickNextButton();
	await page.waitForSelector( '.done' );
}

export function testCloseButton( { exists = true } ) {
	if ( exists ) {
		expect( page ).toMatchElement( 'a', { text: 'Close' } );
	} else {
		expect( page ).not.toMatchElement( 'a', { text: 'Close' } );
	}
}

export function testPreviousButton( { exists = true, disabled = false } ) {
	if ( exists ) {
		expect( page ).toMatchElement( `button${ disabled ? '[disabled]' : '' }`, { text: 'Previous' } );
	} else {
		expect( page ).not.toMatchElement( `button${ disabled ? '[disabled]' : '' }`, { text: 'Close' } );
	}
}

export function testNextButton( { element = 'button', text, disabled = false } ) {
	expect( page ).toMatchElement( `${ element }${ disabled ? '[disabled]' : '' }`, { text } );
}

export function testTitle( { text, element = 'h1' } ) {
	expect( page ).toMatchElement( element, { text } );
}

export async function testElementCount( selector, expected ) {
	const count = await page.$$eval( selector, ( els ) => els.length );
	expect( count ).toBe( expected );
}
