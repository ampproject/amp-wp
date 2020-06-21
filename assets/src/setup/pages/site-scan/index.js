/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useContext, useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Navigation } from '../../components/navigation-context-provider';

/**
 * Screen performing an AMP Site scan.
 */
export function SiteScan() {
	const { canGoForward, setCanGoForward } = useContext( Navigation );

	/**
	 * Allow moving forward.
	 */
	useEffect( () => {
		if ( canGoForward === false ) {
			setCanGoForward( true );
		}
	}, [ canGoForward, setCanGoForward ] );

	return (
		<div>
			{ __( 'Site Scan', 'amp' ) }
		</div>
	);
}
