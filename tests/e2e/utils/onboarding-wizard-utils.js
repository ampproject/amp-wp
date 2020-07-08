/**
 * WordPress dependencies
 */
import { visitAdminPage, isCurrentURL } from '@wordpress/e2e-test-utils';

export const NEXT_BUTTON_SELECTOR = '.onboarding-wizard-nav__prev-next button.is-primary';
export const PREV_BUTTON_SELECTOR = '.onboarding-wizard-nav__prev-next button:not(.is-primary)';

export async function goToOnboardingWizard() {
	if ( ! isCurrentURL( 'admin.php', 'page=amp-onboarding-wizard' ) ) {
		await visitAdminPage( 'admin.php', 'page=amp-onboarding-wizard' );
	}
	await page.waitForSelector( '#amp-onboarding-wizard' );
}

export async function clickNextButton() {
	await page.waitForSelector( `${ NEXT_BUTTON_SELECTOR }:not([disabled])` );
	await expect( page ).toClick( 'button', { text: 'Next' } );
}

export async function clickPrevButton() {
	await page.waitForSelector( `${ PREV_BUTTON_SELECTOR }:not([disabled])` );
	await expect( page ).toClick( 'button', { text: 'Previous' } );
}

export async function moveToTechnicalScreen() {
	await goToOnboardingWizard();
	await clickNextButton();
	await page.waitForSelector( '.technical-background-option' );
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
	await page.$eval( `[for="template-mode-${ mode }"]`, ( el ) => el.click() );
	await page.waitForSelector( `#template-mode-${ mode }:checked` );
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
	if ( mode === 'reader' ) {
		await moveToReaderThemesScreen( [ technical ] );
		await selectReaderTheme( readerTheme );
	} else {
		await moveToTemplateModeScreen( { technical } );
		await clickMode( mode );
	}

	await clickNextButton();

	await page.waitForSelector( '.summary' );
}

export async function completeWizard( { technical = true, mode, readerTheme = 'legacy', mobileRedirect = true } ) {
	await moveToSummaryScreen( { technical, mode, readerTheme, mobileRedirect } );

	if ( 'standard' !== mode ) {
		await page.waitForSelector( '.amp-setting-toggle input' );

		const selector = '.amp-setting-toggle input:checked';
		const checkedMobileRedirect = await page.$( selector );

		if ( checkedMobileRedirect && false === mobileRedirect ) {
			await expect( page ).toClick( selector );
			await page.waitForSelector( '.amp-setting-toggle input:not(:checked)' );
		} else if ( ! checkedMobileRedirect && true === mobileRedirect ) {
			await expect( page ).toClick( selector );
			await page.waitForSelector( selector );
		}
	}

	await clickNextButton();
	await page.waitForSelector( '.done__preview-container' );
}

export async function testCloseButton( { exists = true } ) {
	if ( exists ) {
		await expect( page ).toMatchElement( 'a', { text: 'Close' } );
	} else {
		await expect( page ).not.toMatchElement( 'a', { text: 'Close' } );
	}
}

export async function testPreviousButton( { exists = true, disabled = false } ) {
	if ( exists ) {
		await expect( page ).toMatchElement( `button${ disabled ? '[disabled]' : '' }`, { text: 'Previous' } );
	} else {
		await expect( page ).not.toMatchElement( `button${ disabled ? '[disabled]' : '' }`, { text: 'Previous' } );
	}
}

export function testNextButton( { element = 'button', text, disabled = false } ) {
	expect( page ).toMatchElement( `${ element }${ disabled ? '[disabled]' : '' }`, { text } );
}

export function testTitle( { text, element = 'h1' } ) {
	expect( page ).toMatchElement( element, { text } );
}

/**
 * Reset data modified by the setup wizard.
 */
export async function cleanUpSettings() {
	await page.evaluate( async () => {
		await Promise.all( [
			wp.apiFetch( { path: '/wp/v2/users/me', method: 'POST', data: { amp_dev_tools_enabled: true } } ),
			wp.apiFetch( { path: '/amp/v1/options', method: 'POST', data: {
				mobile_redirect: false,
				reader_theme: 'legacy',
				theme_support: 'reader',
				plugin_configured: false,
			} } ),
		],
		);
	} );
}
