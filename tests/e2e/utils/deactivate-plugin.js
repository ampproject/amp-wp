/**
 * WordPress dependencies
 */
import { visitAdminPage, switchUserToAdmin, switchUserToTest } from '@wordpress/e2e-test-utils';

/**
 * Deactivates an active plugin.
 *
 * Not using the provided deactivatePlugin() utility because it uses page.click(),
 * which does not work if the element is not in the view or obscured by another element
 * like an admin pointer.
 *
 * @param {string} slug Plugin slug.
 */
export async function deactivatePlugin( slug ) {
	await switchUserToAdmin();
	await visitAdminPage( 'plugins.php' );

	await page.evaluate( ( plugin ) => {
		const disableLink = document.querySelector( `tr[data-slug="${ plugin }"] .deactivate a` );

		if ( disableLink ) {
			disableLink.scrollIntoView();
			disableLink.click();
		}
	}, slug );

	await page.waitForSelector( `tr[data-slug="${ slug }"] .delete a` );
	await switchUserToTest();
}
