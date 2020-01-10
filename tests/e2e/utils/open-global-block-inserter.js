/**
 * Opens the global block inserter.
 *
 * The default openGlobalBlockInserter utility from the e2e-test-utils package
 * does not work because the selector there is different from the one in the
 * AMP Stories editor ("Add block" vs. "Add element").
 */
export async function openGlobalBlockInserter() {
	await page.click( '.edit-post-header [aria-label="Add element"]' );
	// Waiting here is necessary because sometimes the inserter takes more time to
	// render than Puppeteer takes to complete the 'click' action
	await page.waitForSelector( '.block-editor-inserter__menu' );
}
