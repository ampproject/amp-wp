/**
 * WordPress dependencies
 */
import { switchUserToTest } from '@wordpress/e2e-test-utils/build/switch-user-to-test';
import { switchUserToAdmin } from '@wordpress/e2e-test-utils/build/switch-user-to-admin';
import { visitAdminPage } from '@wordpress/e2e-test-utils/build/visit-admin-page';

/**
 * Activates an installed theme.
 *
 * @param {string} slug Theme slug.
 */
export async function activateTheme( slug ) {
	await switchUserToAdmin();
	await visitAdminPage( 'themes.php' );

	const activateButton = await page.$( `div[data-slug="${ slug }"] .button.activate` );
	if ( ! activateButton ) {
		switchUserToTest();
		return;
	}

	await page.click( `div[data-slug="${ slug }"] .button.activate` );
	await page.waitForSelector( `div[data-slug="${ slug }"].active` );
	await switchUserToTest();
}
