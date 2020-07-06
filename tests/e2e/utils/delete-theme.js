/**
 * WordPress dependencies
 */
import { switchUserToAdmin, switchUserToTest, visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { installTheme } from './install-theme';
import { activateTheme } from './activate-theme';

/**
 * Installs a theme from the WP.org repository.
 *
 * @param {string} slug        Theme slug.
 * @param {string?} newThemeSlug A theme to switch to if the theme to delete is active. Required if the theme to delete is active.
 * @param {string?} newThemeSearchTerm If the new theme is not findable by its slug, use an alternative term to search.
 */
export async function deleteTheme( slug, newThemeSlug, newThemeSearchTerm ) {
	await switchUserToAdmin();

	if ( newThemeSlug ) {
		await installTheme( newThemeSlug, newThemeSearchTerm );
		await activateTheme( newThemeSlug );
	} else {
		await visitAdminPage( 'themes.php' );
	}

	await page.click( `[data-slug="${ slug }"]` );

	await page.waitForSelector( '.theme-actions .delete-theme' );
	await page.click( '.theme-actions .delete-theme' );

	// Wait for the theme to be removed from the page.
	await page.waitFor( ( themeSlug ) => ! document.querySelector( `[data-slug="${ themeSlug }"]` ), slug );

	await switchUserToTest();
}
