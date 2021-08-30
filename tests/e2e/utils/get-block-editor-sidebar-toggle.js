/**
 * Waits for and returns a block edtior sidebar toggle input and container handles.
 *
 * @param {string} label The toggle label.
 */
export async function getBlockEditorSidebarToggle( label ) {
	const containerXpath = `div[contains(@class, 'components-toggle-control')][.//label[contains(text(), '${ label }')]]`;

	await page.waitForXPath( `//${ containerXpath }` );

	const [ containerHandle ] = await page.$x( `//${ containerXpath }` );
	const [ inputHandle ] = await page.$x( `//input[./ancestor-or-self::${ containerXpath }]` );

	return [ containerHandle, inputHandle ];
}
