/**
 * WordPress dependencies
 */
import { createContext, useEffect, useState } from '@wordpress/element';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { getQueryArg } from '@wordpress/url';

export const SiteScan = createContext();

/**
 * Context provider for site scanning.
 *
 * @param {Object} props Component props.
 * @param {?any} props.children Component children.
 */
export function SiteScanContextProvider( { children } ) {
	const [ themeIssues, setThemeIssues ] = useState( null );
	const [ pluginIssues, setPluginIssues ] = useState( null );
	const [ scanningSite, setScanningSite ] = useState( true );

	/**
	 * @todo This temporary code is for development purposes.
	 */
	useEffect( () => {
		if ( ! scanningSite && ! themeIssues ) {
			setThemeIssues( getQueryArg( global.location.href, 'amp-theme-issues' ) ? [ 'Theme issue 1' ] : null ); // URL param is for testing.
		}
	}, [ scanningSite, themeIssues ] );

	/**
	 * @todo This temporary code is for development purposes.
	 */
	useEffect( () => {
		if ( ! scanningSite && ! pluginIssues ) {
			setPluginIssues( getQueryArg( global.location.href, 'amp-plugin-issues' ) ? [ 'Plugin issue 1' ] : null ); // URL param is for testing.
		}
	}, [ scanningSite, pluginIssues ] );

	/**
	 * @todo This temporary code is for development purposes.
	 */
	useEffect( () => {
		if ( true === scanningSite ) {
			setScanningSite( false );
		}
	}, [ scanningSite ] );

	return (
		<SiteScan.Provider
			value={
				{
					pluginIssues,
					scanningSite,
					themeIssues,
				}
			}
		>
			{ children }
		</SiteScan.Provider>
	);
}

SiteScanContextProvider.propTypes = {
	children: PropTypes.any,
};
