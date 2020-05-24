/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Screen showing site configuration details.
 */
export function SiteConfigurationSummary() {
	return (
		<div>
			{ __( 'Site Configuration Summary', 'amp' ) }
		</div>
	);
}
