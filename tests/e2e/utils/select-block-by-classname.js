/**
 * Select block in editor by clicking on it.
 *
 * @param {string} className Class name to select by.
 */
export async function selectBlockByClassName( className ) {
	// We have to select the page first and then the block inside.
	await page.click( '.amp-page-active' );
	await page.click( `.${ className }` );
}
