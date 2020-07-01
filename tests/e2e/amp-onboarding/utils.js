/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils/build/visit-admin-page';

export const NEXT_BUTTON_SELECTOR = '.amp-setup-nav__prev-next button.is-primary';
export const PREV_BUTTON_SELECTOR = '.amp-setup-nav__prev-next button:not(.is-primary)';

export async function clickNextButton() {
	await page.waitForSelector( `${ NEXT_BUTTON_SELECTOR }:not([disabled])` );
	await page.click( NEXT_BUTTON_SELECTOR );
}

export async function clickPrevButton() {
	await page.waitForSelector( `${ PREV_BUTTON_SELECTOR }:not([disabled])` );
	await page.click( PREV_BUTTON_SELECTOR );
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
	await page.waitForSelector( '.choose-reader-theme' );
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
