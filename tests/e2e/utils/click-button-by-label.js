/**
 * Search for block in the global inserter
 *
 * @param {string} label Aria label.
 */
export async function clickButtonByLabel( label ) {
	const btnSelector = `button[aria-label="${ label }"]`;
	await page.waitForSelector( btnSelector );
	await page.evaluate( ( selector ) => {
		document.querySelector( selector ).click();
	}, btnSelector );
}
