/**
 * Internal dependencies
 */
import { scrollToElement } from './onboarding-wizard-utils';

export async function setTemplateMode( mode ) {
	// Set template mode.
	const modeSelector = `#template-mode-${ mode }-container input`;

	await page.waitForSelector( modeSelector );
	await scrollToElement( { selector: modeSelector, click: true } );

	// Save options and wait for the request to succeed.
	const saveButtonSelector = '.amp-settings-nav button[type="submit"]';

	await page.waitForSelector( saveButtonSelector );
	await scrollToElement( { selector: saveButtonSelector, click: true } );

	await page.waitForResponse( ( response ) => response.url().includes( '/wp-json/amp/v1/options' ) );
}
