/**
 * Utils file for stories-editor tests.
 */

/**
 * Opens the inserter, searches the block and inserts it.
 * Default helpers from WP won't work due to changed selectors.
 *
 * @param {string} searchTerm Search term to find the block by.
 * @return {Promise<void>} Promise.
 */
export async function insertStoryBlockBySearch( searchTerm ) {
	await page.click( '.edit-post-header [aria-label="Add element"]' );
	// Waiting here is necessary to ensure Puppeteer has opened the inserter after click.
	await page.waitForSelector( '.block-editor-inserter__menu' );
	await page.keyboard.type( searchTerm );
	const insertButton = ( await page.$x(
		`//button//span[contains(text(), '${ searchTerm }')]`
	) )[ 0 ];
	await insertButton.click();
}
