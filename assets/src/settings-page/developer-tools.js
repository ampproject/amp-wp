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
			<h4>
				{ __( 'Dev Tools', 'amp' ) }
			</h4>
			<p>
				{ __( 'This will only be enabled for your user account. This is not a sitewide setting.', 'amp' ) }
			</p>
			<DevToolsToggle />
		</section>
	);
}
