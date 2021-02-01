/**
 * WordPress dependencies
 */
import { useContext, useEffect, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './style.css';
import { Navigation } from '../../components/navigation-context-provider';
import { Options } from '../../../components/options-context-provider';
import { ReaderThemes } from '../../../components/reader-themes-context-provider';
import { Reader } from './reader';
import { Standard } from './standard';
import { Transitional } from './transitional';

/**
 * Screen showing site configuration details.
 */
export function Summary() {
	const { setCanGoForward } = useContext( Navigation );
	const { editedOptions } = useContext( Options );
	const { currentTheme } = useContext( ReaderThemes );

	const { theme_support: themeSupport } = editedOptions;

	/**
	 * Allow moving forward.
	 */
	useEffect( () => {
		setCanGoForward( true );
	}, [ setCanGoForward ] );

	const Screen = useCallback( () => {
		if ( undefined === themeSupport ) {
			return null;
		}

		switch ( themeSupport ) {
			case 'reader':
				return <Reader currentTheme={ currentTheme } />;

			case 'standard':
				return <Standard currentTheme={ currentTheme } />;

			case 'transitional':
				return <Transitional currentTheme={ currentTheme } />;

			default:
				throw new Error( __( 'A mode option was not accounted for on the summary screen.', 'amp' ) );
		}
	}, [ currentTheme, themeSupport ] );

	return (
		<div className="summary">
			<Screen />
		</div>
	);
}
