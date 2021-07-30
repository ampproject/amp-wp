/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { DevToolsToggle } from '../components/dev-tools-toggle';

/**
 * Developer tools section of the settings page.
 */
export function DeveloperTools() {
	return (
		<section className="developer-tools">
			<DevToolsToggle />
			<p>
			{ __( 'Only enabled for your user account. This is not a sitewide setting.', 'amp' ) }
			</p>
		</section>
	);
}
