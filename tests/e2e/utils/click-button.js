/**
 * Clicks a button based on the text on the button.
 *
 * This is almost a copy of the upstream util, however, it uses page.evaluate for clicking since it seems to work more reliably.
 *
 * @param {string} buttonText The text that appears on the button to click.
 */
export async function clickButton( buttonText ) {
	const button = await page.waitForXPath( `//button[contains(text(), '${ buttonText }')]` );
	await page.evaluate( ( btn ) => {
		btn.click();
	}, button );
}
