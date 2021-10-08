/**
 * WordPress dependencies
 */
import { createContext, useCallback, useEffect, useReducer, useRef } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

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

const ACTION_SCANNABLE_URLS_REQUEST = 'ACTION_SCANNABLE_URLS_REQUEST';
const ACTION_SCANNABLE_URLS_FETCH = 'ACTION_SCANNABLE_URLS_FETCH';
const ACTION_SCANNABLE_URLS_RECEIVE = 'ACTION_SCANNABLE_URLS_RECEIVE';
const ACTION_START_SITE_SCAN = 'ACTION_START_SITE_SCAN';
const ACTION_SCAN_IN_PROGRESS = 'ACTION_SCAN_IN_PROGRESS';
const ACTION_SCAN_RECEIVE_ISSUES = 'ACTION_SCAN_RECEIVE_ISSUES';
const ACTION_SCAN_NEXT_URL = 'ACTION_SCAN_NEXT_URL';
const ACTION_SCAN_CANCEL = 'ACTION_SCAN_CANCEL';

const STATUS_REQUEST_SCANNABLE_URLS = 'STATUS_REQUEST_SCANNABLE_URLS';
const STATUS_FETCHING_SCANNABLE_URLS = 'STATUS_FETCHING_SCANNABLE_URLS';
const STATUS_READY = 'STATUS_READY';
const STATUS_IDLE = 'STATUS_IDLE';
const STATUS_IN_PROGRESS = 'STATUS_IN_PROGRESS';
const STATUS_COMPLETE = 'STATUS_COMPLETE';

function siteScanReducer( state, action ) {
	switch ( action.type ) {
		case ACTION_SCANNABLE_URLS_REQUEST: {
			return {
				...state,
				status: STATUS_REQUEST_SCANNABLE_URLS,
			};
		}
		case ACTION_SCANNABLE_URLS_FETCH: {
			return {
				...state,
				status: STATUS_FETCHING_SCANNABLE_URLS,
			};
		}
		case ACTION_SCANNABLE_URLS_RECEIVE: {
			return {
				...state,
				status: STATUS_READY,
				scannableUrls: action.scannableUrls,
			};
		}
		case ACTION_START_SITE_SCAN: {
			return {
				...state,
				status: STATUS_IDLE,
				themeIssues: [],
				pluginIssues: [],
				currentlyScannedUrlIndex: initialState.currentlyScannedUrlIndex,
			};
		}
		case ACTION_SCAN_IN_PROGRESS: {
			return {
				...state,
				status: STATUS_IN_PROGRESS,
			};
		}
		case ACTION_SCAN_RECEIVE_ISSUES: {
			return {
				...state,
				pluginIssues: [ ...new Set( [ ...state.pluginIssues, ...action.pluginIssues ] ) ],
				themeIssues: [ ...new Set( [ ...state.themeIssues, ...action.themeIssues ] ) ],
			};
		}
		case ACTION_SCAN_NEXT_URL: {
			const hasNextUrl = state.currentlyScannedUrlIndex < state.scannableUrls.length - 1;
			return {
				...state,
				status: hasNextUrl ? STATUS_IDLE : STATUS_COMPLETE,
				currentlyScannedUrlIndex: hasNextUrl ? state.currentlyScannedUrlIndex + 1 : state.currentlyScannedUrlIndex,
			};
		}
		case ACTION_SCAN_CANCEL: {
			return {
				...state,
				status: STATUS_READY,
			};
		}
		default: {
			throw new Error( `Unhandled action type: ${ action.type }` );
		}
	}
}

const initialState = {
	themeIssues: [],
	pluginIssues: [],
	status: '',
	scannableUrls: [],
	currentlyScannedUrlIndex: 0,
};

/**
 * Context provider for site scanning.
 *
 * @param {Object} props                       Component props.
 * @param {?any}   props.children              Component children.
 * @param {string} props.scannableUrlsRestPath The REST path for interacting with the scannable URL resources.
 * @param {string} props.validateNonce         The AMP validate nonce.
 * @param {string} props.validateQueryVar      The AMP validate query variable name.
 */
export function SiteScanContextProvider( {
	children,
	scannableUrlsRestPath,
	validateNonce,
	validateQueryVar,
} ) {
	const { setAsyncError } = useAsyncError();
	const [ state, dispatch ] = useReducer( siteScanReducer, initialState );
	const {
		currentlyScannedUrlIndex,
		pluginIssues,
		scannableUrls,
		status,
		themeIssues,
	} = state;

	useEffect( () => {
		if ( status ) {
			return;
		}

		if ( ! validateQueryVar || ! validateNonce ) {
			throw new Error( 'Invalid site scan configuration' );
		}

		dispatch( { type: ACTION_SCANNABLE_URLS_REQUEST } );
	}, [ status, validateNonce, validateQueryVar ] );

	/**
	 * This component sets state inside async functions. Use this ref to prevent
	 * state updates after unmount.
	 */
	const hasUnmounted = useRef( false );
	useEffect( () => () => {
		hasUnmounted.current = true;
	}, [] );

	const startSiteScan = useCallback( () => {
		if ( status === STATUS_READY ) {
			dispatch( { type: ACTION_START_SITE_SCAN } );
		}
	}, [ status ] );

	/**
	 * Allows cancelling a scan that is in progress.
	 */
	const hasCanceled = useRef( false );
	const cancelSiteScan = useCallback( () => {
		hasCanceled.current = true;
	}, [] );

	/**
	 * Fetch scannable URLs from the REST endpoint.
	 */
	useEffect( () => {
		if ( status !== STATUS_REQUEST_SCANNABLE_URLS ) {
			return;
		}

		( async () => {
			dispatch( { type: ACTION_SCANNABLE_URLS_FETCH } );

			try {
				const response = await apiFetch( {
					path: scannableUrlsRestPath,
				} );

				if ( true === hasUnmounted.current ) {
					return;
				}

				dispatch( {
					type: ACTION_SCANNABLE_URLS_RECEIVE,
					scannableUrls: response,
				} );
			} catch ( e ) {
				setAsyncError( e );
			}
		} )();
	}, [ scannableUrlsRestPath, setAsyncError, status ] );

	/**
	 * Scan site URLs sequentially.
	 */
	useEffect( () => {
		if ( status !== STATUS_IDLE ) {
			return;
		}

		/**
		 * Validates the next URL in the queue.
		 */
		( async () => {
			dispatch( { type: ACTION_SCAN_IN_PROGRESS } );

			try {
				const { url } = scannableUrls[ currentlyScannedUrlIndex ];
				const validationResults = await apiFetch( {
					url: addQueryArgs( url, {
						'amp-first': true,
						[ validateQueryVar ]: {
							nonce: validateNonce,
							omit_stylesheets: true,
						},
					} ),
				} );

				if ( true === hasUnmounted.current ) {
					return;
				}

				if ( true === hasCanceled.current ) {
					hasCanceled.current = false;
					dispatch( { type: ACTION_SCAN_CANCEL } );

					return;
				}

				if ( validationResults.results.length > 0 ) {
					const siteIssues = getSiteIssues( validationResults.results );

					dispatch( {
						type: ACTION_SCAN_RECEIVE_ISSUES,
						themeIssues: siteIssues.themeIssues,
						pluginIssues: siteIssues.pluginIssues,
					} );
				}

				dispatch( { type: ACTION_SCAN_NEXT_URL } );
			} catch ( e ) {
				setAsyncError( e );
			}
		} )();
	}, [ currentlyScannedUrlIndex, scannableUrls, setAsyncError, status, validateNonce, validateQueryVar ] );

	return (
		<SiteScan.Provider
			value={ {
				cancelSiteScan,
				currentlyScannedUrlIndex,
				isBusy: [ STATUS_IDLE, STATUS_IN_PROGRESS ].includes( status ),
				isComplete: status === STATUS_COMPLETE,
				isInitializing: [ STATUS_REQUEST_SCANNABLE_URLS, STATUS_FETCHING_SCANNABLE_URLS ].includes( status ),
				isReady: status === STATUS_READY,
				pluginIssues,
				scannableUrls,
				startSiteScan,
				themeIssues,
			} }
		>
			{ children }
		</SiteScan.Provider>
	);
}

SiteScanContextProvider.propTypes = {
	children: PropTypes.any,
	scannableUrlsRestPath: PropTypes.string,
	validateNonce: PropTypes.string,
	validateQueryVar: PropTypes.string,
};
