/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useEffect, useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Navigation } from '../../components/navigation-context-provider';

/**
 * Screen for selecting the template mode.
 */
export function TemplateMode() {
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
			{ __( 'Template Mode', 'amp' ) }
		</div>
	);
}
