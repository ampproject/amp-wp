/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { SimplePanel } from '../../panels/panel';

function PrepublishInspector() {
	return (
		<SimplePanel title={ __( 'Prepublish', 'amp' ) }>
			{ __( 'Prepublish panel to be implemented here', 'amp' ) }
		</SimplePanel>
	);
}

export default PrepublishInspector;
