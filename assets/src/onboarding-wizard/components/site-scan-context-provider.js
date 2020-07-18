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
	 * @todo Note: The following effects will be updated for version 2.1 when site scan is implemented in the wizard. For now,
	 * we will keep themeIssues and pluginIssues set to null, emulating an unsuccessful site scan. The wizard will then make
	 * a mode recommendation based only on how the user has answered the technical question.
	 */
	useEffect( () => {
		if ( ! scanningSite && ! themeIssues ) {
			setThemeIssues( getQueryArg( global.location.href, 'amp-theme-issues' ) ? [ 'Theme issue 1' ] : null ); // URL param is for testing.
		}
	}, [ scanningSite, themeIssues ] );

	// See note above.
	useEffect( () => {
		if ( ! scanningSite && ! pluginIssues ) {
			setPluginIssues( getQueryArg( global.location.href, 'amp-plugin-issues' ) ? [ 'Plugin issue 1' ] : null ); // URL param is for testing.
		}
	}, [ scanningSite, pluginIssues ] );

	// See note above.
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
