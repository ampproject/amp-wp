/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Activate a given AMP experience.
 *
 * @param {('website'|'stories')} experience AMP experience to activate. Either 'website' or 'stories'.
 */
export async function activateExperience( experience ) {
	await visitAdminPage( 'admin.php', 'page=amp-options' );

	const selector = `#${ experience }_experience`;
	const isChecked = await page.$eval( selector, ( el ) => el.matches( `:checked` ) );

	if ( isChecked ) {
		return;
	}

	await page.click( selector );
	await page.click( '#submit' );
	await page.waitForNavigation();
}
