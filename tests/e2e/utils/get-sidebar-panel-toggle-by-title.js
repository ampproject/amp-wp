/**
 * Get the first matching toggle button from sidebar panel.
 *
 * @param {string} panelTitle Aria label.
 */
export async function getSidebarPanelToggleByTitle( panelTitle ) {
	const selector = `//button[@class="components-button components-panel__body-toggle"]//span[contains(text(),"${ panelTitle }")]`;
	await page.waitForXPath( selector );

	const toggles = await page.$x( selector );
	return toggles[ 0 ];
}
