/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Sets the global story settings to specified values.
 *
 * @param {string} advanceAfterValue         Advancement setting value.
 * @param {string} advanceAfterDurationValue Advancement duration setting value.
 */
export async function setStorySettings( advanceAfterValue, advanceAfterDurationValue ) {
	const storiesExportSelector = '#story_export_base_url';
	const advanceAfterSelector = '#stories_settings_auto_advance_after';
	const advanceAfterDurationSelector = '#stories_settings_auto_advance_after_duration';

	await visitAdminPage( 'admin.php', 'page=amp-options' );

	await page.evaluate( ( exportSel, durationSel ) => {
		document.querySelector( exportSel ).value = '';
		document.querySelector( durationSel ).value = '';
	}, storiesExportSelector, advanceAfterDurationSelector );

	await page.select( advanceAfterSelector, advanceAfterValue );

	const advanceAfterDurationElement = await page.$( advanceAfterDurationSelector );

	await advanceAfterDurationElement.type( advanceAfterDurationValue );
	await advanceAfterDurationElement.press( 'Enter' );
	await page.waitForSelector( '#setting-error-settings_updated' );
}
