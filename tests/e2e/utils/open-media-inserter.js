/**
 * Helper function to open media inserter dropdown.
 *
 */
export async function openMediaInserter() {
	await page.waitForSelector( '.amp-story-media-inserter-dropdown' );
	await page.click( '.amp-story-media-inserter-dropdown' );
}
