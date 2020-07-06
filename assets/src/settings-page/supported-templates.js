
/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { SupportedTemplatesToggle } from '../components/supported-templates-toggle';

export function SupportedTemplates() {
	return (
		<div className="supported-templates">

			<h2>
				{ __( 'Supported Templates', 'amp' ) }
			</h2>
			<SupportedTemplatesToggle />
		</div>
	);
}
