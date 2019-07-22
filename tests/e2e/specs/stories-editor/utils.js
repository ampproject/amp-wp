/**
 * Utils file for stories-editor tests.
 */

/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Opens the inserter, searches the block and inserts it.
 * Default `insertBlock` from WP won't work since it's using "Add block" aria-label as selector.
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

/**
 * Toggles Stories feature being enabled.
 *
 * @param {boolean} shouldCheck If should enable the Stories.
 * @return {Promise<void>} Promise.
 */
export async function toggleStories( shouldCheck = undefined ) {
	await visitAdminPage( 'admin.php', 'page=amp-options' );

	const isChecked = await page.$eval( '#stories_experience', ( el ) => el.matches( `:checked` ) );

	if ( isChecked !== shouldCheck ) {
		await page.click( '#stories_experience' );
		await page.click( '#submit' );
		await page.waitForNavigation();
	}
}
