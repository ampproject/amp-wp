/**
 * WordPress dependencies
 */
import { createContext, useEffect, useRef, useState } from '@wordpress/element';
import { getQueryArg } from '@wordpress/url';
import apiFetch from '@wordpress/api-fetch';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { useAsyncError } from '../../utils/use-async-error';

export const SiteScan = createContext();

/**
 * Context provider for site scanning.
 *
 * @param {Object} props                       Component props.
 * @param {?any}   props.children              Component children.
 * @param {string} props.scannableUrlsRestPath The REST path for interacting with the scannable URL resources.
 */
export function SiteScanContextProvider( {
	children,
	scannableUrlsRestPath,
} ) {
	const [ themeIssues, setThemeIssues ] = useState( null );
	const [ pluginIssues, setPluginIssues ] = useState( null );
	const [ scanningSite, setScanningSite ] = useState( true );
	const [ fetchingScannableUrls, setFetchingScannableUrls ] = useState( false );
	const [ scannableUrls, setScannableUrls ] = useState( [] );
	const { setAsyncError } = useAsyncError();

	// This component sets state inside async functions. Use this ref to prevent state updates after unmount.
	const hasUnmounted = useRef( false );
	useEffect( () => () => {
		hasUnmounted.current = true;
	}, [] );

	useEffect( () => {
		if ( fetchingScannableUrls || scannableUrls.length > 0 ) {
			return;
		}

		/**
		 * Fetches scannable URLs from the REST endpoint.
		 */
		( async () => {
			setFetchingScannableUrls( true );

			try {
				const fetchedScannableUrls = await apiFetch( {
					path: scannableUrlsRestPath,
				} );

				if ( true === hasUnmounted.current ) {
					return;
				}

				setScannableUrls( fetchedScannableUrls );
			} catch ( e ) {
				setAsyncError( e );
				return;
			}

			setFetchingScannableUrls( false );
		} )();
	}, [ fetchingScannableUrls, scannableUrlsRestPath, scannableUrls.length, setAsyncError ] );

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
					fetchingScannableUrls,
					pluginIssues,
					scanningSite,
					scannableUrls,
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
	scannableUrlsRestPath: PropTypes.string,
};
