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
 * Screen for selecting the user's technical background.
 */
export function TechnicalBackground() {
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
			{ __( 'Technical Background', 'amp' ) }
		</div>
	);
}
