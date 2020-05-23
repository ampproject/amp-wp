/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Screen for choosing the Reader theme.
 */
export function ChooseReaderTheme() {
	return (
		<div>
			{ __( 'Choose Reader Theme', 'amp' ) }
		</div>
	);
}
