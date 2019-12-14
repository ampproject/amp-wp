/**
 * WordPress dependencies
 */
import { switchUserToAdmin, switchUserToTest, visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Activates an installed plugin.
 *
 * Not using the provided activatePlugin() utility because it uses page.click(),
 * which does not work if the element is not in the view or obscured by another element
 * like an admin pointer.
 *
 * @param {string} slug Plugin slug.
 */
export async function activatePlugin( slug ) {
	await switchUserToAdmin();
	await visitAdminPage( 'plugins.php' );

	const disableLink = await page.$( `tr[data-slug="${ slug }"] .deactivate a` );
	if ( disableLink ) {
		return;
	}

	await page.evaluate( ( plugin ) => {
		const enableLink = document.querySelector( `tr[data-slug="${ plugin }"] .activate a` );

		if ( enableLink ) {
			enableLink.scrollIntoView();
			enableLink.click();
		}
	}, slug );

	await page.waitForSelector( `tr[data-slug="${ slug }"] .deactivate a` );
	await switchUserToTest();
}
