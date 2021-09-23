/**
 * WordPress dependencies
 */
import { createContext, useEffect, useRef, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { useAsyncError } from '../../utils/use-async-error';
import { getSiteIssues } from './get-site-issues';

export const SiteScan = createContext();

const SITE_SCAN_STATE_INITIALIZING = 'SITE_SCAN_STATE_INITIALIZING';
const SITE_SCAN_STATE_READY = 'SITE_SCAN_STATE_READY';
const SITE_SCAN_STATE_IDLE = 'SITE_SCAN_STATE_IDLE';
const SITE_SCAN_STATE_IN_PROGRESS = 'SITE_SCAN_STATE_IN_PROGRESS';
const SITE_SCAN_STATE_COMPLETE = 'SITE_SCAN_STATE_COMPLETE';

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
	const [ themeIssues, setThemeIssues ] = useState( [] );
	const [ pluginIssues, setPluginIssues ] = useState( [] );
	const [ siteScanState, setSiteScanState ] = useState( SITE_SCAN_STATE_INITIALIZING );
	const [ currentlyScannedUrlIndex, setCurrentlyScannedUrlIndex ] = useState( 0 );
	const [ fetchingScannableUrls, setFetchingScannableUrls ] = useState( false );
	const [ fetchedScannableUrls, setFetchedScannableUrls ] = useState( false );
	const [ scannableUrls, setScannableUrls ] = useState( [] );
	const { setAsyncError } = useAsyncError();

	// This component sets state inside async functions. Use this ref to prevent state updates after unmount.
	const hasUnmounted = useRef( false );
	useEffect( () => () => {
		hasUnmounted.current = true;
	}, [] );

	useEffect( () => {
		if ( fetchingScannableUrls || fetchedScannableUrls ) {
			return;
		}

		/**
		 * Fetches scannable URLs from the REST endpoint.
		 */
		( async () => {
			setFetchingScannableUrls( true );

			try {
				const response = await apiFetch( {
					path: scannableUrlsRestPath,
				} );

				if ( true === hasUnmounted.current ) {
					return;
				}

				setScannableUrls( response );
				setSiteScanState( SITE_SCAN_STATE_READY );
				setFetchedScannableUrls( true );
			} catch ( e ) {
				setAsyncError( e );
				return;
			}

			setFetchingScannableUrls( false );
		} )();
	}, [ fetchedScannableUrls, fetchingScannableUrls, scannableUrlsRestPath, setAsyncError ] );

	/**
	 * Scan site URLs sequentially.
	 */
	useEffect( () => {
		if ( siteScanState !== SITE_SCAN_STATE_IDLE ) {
			return;
		}

		/**
		 * Validates the next URL in the queue.
		 */
		( async () => {
			setSiteScanState( SITE_SCAN_STATE_IN_PROGRESS );

			try {
				const { validate_url: validateUrl } = scannableUrls[ currentlyScannedUrlIndex ];
				const validationResults = await apiFetch( { url: validateUrl } );

				if ( true === hasUnmounted.current ) {
					return;
				}

				if ( validationResults.results.length > 0 ) {
					const siteIssues = getSiteIssues( validationResults.results );

					setPluginIssues( ( issues ) => [ ...new Set( [ ...issues, ...siteIssues.pluginIssues ] ) ] );
					setThemeIssues( ( issues ) => [ ...new Set( [ ...issues, ...siteIssues.themeIssues ] ) ] );
				}
			} catch ( e ) {
				setAsyncError( e );
				return;
			}

			/**
			 * Finish the scan if there are no more URLs to validate.
			 */
			if ( currentlyScannedUrlIndex === scannableUrls.length - 1 ) {
				setSiteScanState( SITE_SCAN_STATE_COMPLETE );
			} else {
				setCurrentlyScannedUrlIndex( ( index ) => index + 1 );
				setSiteScanState( SITE_SCAN_STATE_IDLE );
			}
		} )();
	}, [ currentlyScannedUrlIndex, scannableUrls, setAsyncError, siteScanState ] );

	return (
		<SiteScan.Provider
			value={
				{
					canScanSite: siteScanState === SITE_SCAN_STATE_READY,
					currentlyScannedUrlIndex,
					fetchingScannableUrls,
					pluginIssues,
					scannableUrls,
					siteScanComplete: siteScanState === SITE_SCAN_STATE_COMPLETE,
					startSiteScan: () => setSiteScanState( SITE_SCAN_STATE_IDLE ),
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
