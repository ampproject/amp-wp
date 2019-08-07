/**
 * External dependencies
 */
import { last } from 'lodash';

/**
 * Opens and returns preview Page.
 *
 * @param {Object} editorPage Editor page.
 * @param {string} selector Selector to wait for.
 * @return {Promise<*>} Preview Page.
 */
export async function openPreviewPage( editorPage, selector ) {
	let openTabs = await browser.pages();
	const expectedTabsCount = openTabs.length + 1;
	await editorPage.click( '.editor-post-preview' );

	// Wait for the new tab to open.
	while ( openTabs.length < expectedTabsCount ) {
		await editorPage.waitFor( 1 ); // eslint-disable-line no-await-in-loop
		openTabs = await browser.pages(); // eslint-disable-line no-await-in-loop
	}

	const previewPage = last( openTabs );
	// Wait for the preview to load. We can't do interstitial detection here,
	// because it might load too quickly for us to pick up, so we wait for
	// the preview to load by waiting for the title to appear.
	await previewPage.waitForSelector( selector );
	return previewPage;
}
