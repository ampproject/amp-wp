/**
 * Switches editor mode.
 *
 * This is almost a copy from upstream switchEditorModeTo util, however, it uses page.evaluate for clicking
 * since that seems to work more reliably.
 *
 * @param {string} mode String editor mode.
 */
export async function switchEditorModeTo( mode ) {
	const selector = '.edit-post-more-menu [aria-label="More tools & options"]';
	await page.waitForSelector( selector );
	await page.evaluate( ( sel ) => {
		document.querySelector( sel ).click();
	}, selector );
	const [ button ] = await page.$x(
		`//button[contains(text(), '${ mode } Editor')]`
	);

	await page.evaluate( ( btn ) => {
		btn.click( 'button' );
	}, button );
}
