/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Resets the global story settings to the default values.
 */
export async function resetStorySettings() {
	const storiesExportSelector = '#story_export_base_url';
	const advanceAfterSelector = '#stories_settings_auto_advance_after';
	const advanceAfterDurationSelector = '#stories_settings_auto_advance_after_duration';

	await visitAdminPage( 'admin.php', 'page=amp-options' );

	await page.evaluate( ( exportSel, durationSel ) => {
		document.querySelector( exportSel ).value = '';
		document.querySelector( durationSel ).value = '';
	}, storiesExportSelector, advanceAfterDurationSelector );

	await page.select( advanceAfterSelector, '' );

	const advanceAfterDurationElement = await page.$( advanceAfterDurationSelector );

	await advanceAfterDurationElement.type( '0' );
	await advanceAfterDurationElement.press( 'Enter' );
}
