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
	await page.waitForSelector( 'input[type="radio"]' );
}

export async function moveToTemplateModeScreen( { technical = true } ) {
	await moveToTechnicalScreen();

	const radioSelector = technical ? '#technical-background-enable' : '#technical-background-disable';

	await page.waitForSelector( radioSelector );
	await page.$eval( radioSelector, ( el ) => el.click() );

	await clickNextButton();
	await page.waitForSelector( 'input[type="radio"]' );
}
