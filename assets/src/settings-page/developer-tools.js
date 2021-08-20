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
				{ __( 'Enable AMP developer tools to surface validation errors when editing posts and viewing the site.', 'amp' ) }
			</p>
			<p>
				{ __( 'Only enabled for your user account. This is not a sitewide setting. This presumes you have some experience coding with HTML, CSS, JS, and PHP.', 'amp' ) }
			</p>
		</section>
	);
}
