/**
 * WordPress dependencies
 */
import { useContext, useEffect } from '@wordpress/element';

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

	switch ( themeSupport ) {
		case 'reader':
			return <Reader currentTheme={ CURRENT_THEME } />;

		case 'standard':
			return <Standard />;

		case 'transitional':
			return <Transitional />;

		default:
			return null;
	}
}
