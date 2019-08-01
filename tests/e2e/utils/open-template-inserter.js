/**
 * Opens the AMP Stories template inserter.
 */
export async function openTemplateInserter() {
	await page.click( '.block-editor-inserter .editor-inserter__amp-inserter' );
	// Waiting here is necessary because sometimes the inserter takes more time to
	// render than Puppeteer takes to complete the 'click' action
	await page.waitForSelector( '.amp-stories__editor-inserter__menu' );
}
