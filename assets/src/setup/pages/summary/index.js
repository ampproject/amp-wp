/**
 * WordPress dependencies
 */
import { useContext, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import { CURRENT_THEME } from 'amp-setup'; // From WP inline script.

/**
 * Internal dependencies
 */
import './style.css';
import { Navigation } from '../../components/navigation-context-provider';
import { Options } from '../../components/options-context-provider';
import { Reader } from './reader';
import { Standard } from './standard';
import { Transitional } from './transitional';

/**
 * Screen showing site configuration details.
 */
export function Summary() {
	const { setCanGoForward } = useContext( Navigation );
	const { options } = useContext( Options );

	const { theme_support: themeSupport } = options || {};

	/**
	 * Allow moving forward.
	 */
	useEffect( () => {
		setCanGoForward( true );
	}, [ setCanGoForward ] );

	if ( undefined === themeSupport ) {
		return null;
	}

	switch ( themeSupport ) {
		case 'reader':
			return <Reader currentTheme={ CURRENT_THEME } />;

		case 'standard':
			return <Standard currentTheme={ CURRENT_THEME } />;

		case 'transitional':
			return <Transitional currentTheme={ CURRENT_THEME } />;

		default:
			throw new Error( __( 'A mode option was not accounted for on the summary screen.', 'amp' ) );
	}
}
