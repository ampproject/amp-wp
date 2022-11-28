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
				{ __( 'This is a per-user setting. It presumes you have some experience coding with HTML, CSS, JS, and PHP. Once enabled you will have access to Validated URLs and Error Index in the admin menu, the Validate URL item in the admin bar, and the AMP Validation sidebar in the editor.', 'amp' ) }
			</p>
		</section>
	);
}
