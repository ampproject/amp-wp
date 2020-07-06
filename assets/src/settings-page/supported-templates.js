
/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Options } from '../components/options-context-provider';

export function SupportedTemplates() {
	const { editedOptions } = useContext( Options );

	return (
		<div className="supported-templates">

			<h2>
				{ __( 'Supported Templates', 'amp' ) }
			</h2>
		</div>
	);
}
