/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Screen performing an AMP site scan.
 */
export function SiteScan() {
	return (
		<div>
			{ __( 'Site Scan', 'amp' ) }
		</div>
	);
}
