/**
 * Go to previous Page.
 */
export async function goToPreviousPage() {
	const btnSelector = 'button[aria-label="Previous Page"]';
	await page.waitForSelector( btnSelector );
	await page.evaluate( ( selector ) => {
		document.querySelector( selector ).click();
	}, btnSelector );
}
