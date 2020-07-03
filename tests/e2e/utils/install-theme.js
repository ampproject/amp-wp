/**
 * Internal dependencies
 */

/**
 * WordPress dependencies
 */
import { switchUserToAdmin } from '@wordpress/e2e-test-utils/build/switch-user-to-admin';
import { switchUserToTest } from '@wordpress/e2e-test-utils/build/switch-user-to-test';
import { visitAdminPage } from '@wordpress/e2e-test-utils/build/visit-admin-page';

/**
 * Installs a theme from the WP.org repository.
 *
 * @param {string} slug        Theme slug.
 * @param {string?} searchTerm If the theme is not findable by its slug use an alternative term to search.
 */
export async function installTheme( slug, searchTerm ) {
	await switchUserToAdmin();
	await visitAdminPage(
		'theme-install.php',
		`search=${ encodeURIComponent( searchTerm || slug ) }`,
	);
	await page.waitForSelector( `div[data-slug="${ slug }"]` );

	const activateLink = await page.$( `div[data-slug="${ slug }"] .button.activate` );
	if ( activateLink ) {
		switchUserToTest();
		return;
	}

	await page.click( `.theme-install[data-slug="${ slug }"]` );
	await page.waitForSelector( `.theme[data-slug="${ slug }"] .activate` );
	await switchUserToTest();
}
