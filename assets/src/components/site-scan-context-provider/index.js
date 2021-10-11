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
			if ( ! action?.scannableUrls?.length || action.scannableUrls.length === 0 ) {
				return {
					...state,
					status: STATUS_COMPLETE,
				};
			}

			const validationErrors = action.scannableUrls.reduce( ( acc, data ) => [ ...acc, ...data?.validation_errors ?? [] ], [] );
			const siteIssues = getSiteIssues( validationErrors );

			return {
				...state,
				status: STATUS_READY,
				scannableUrls: action.scannableUrls,
				stale: Boolean( action.scannableUrls.find( ( error ) => error?.stale === true ) ),
				pluginIssues: [ ...new Set( [ ...state.pluginIssues, ...siteIssues.pluginIssues ] ) ],
				themeIssues: [ ...new Set( [ ...state.themeIssues, ...siteIssues.themeIssues ] ) ],
			};
		}
		case ACTION_START_SITE_SCAN: {
			return {
				...state,
				status: STATUS_IDLE,
				stale: false,
				cache: action.cache,
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
			if ( ! action?.validationResults?.length || action.validationResults.length === 0 ) {
				return state;
			}

			const siteIssues = getSiteIssues( action.validationResults );

			return {
				...state,
				pluginIssues: [ ...new Set( [ ...state.pluginIssues, ...siteIssues.pluginIssues ] ) ],
				themeIssues: [ ...new Set( [ ...state.themeIssues, ...siteIssues.themeIssues ] ) ],
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
	stale: false,
	cache: false,
	currentlyScannedUrlIndex: 0,
};

/**
 * Context provider for site scanning.
 *
 * @param {Object}  props                             Component props.
 * @param {?any}    props.children                    Component children.
 * @param {boolean} props.fetchCachedValidationErrors Whether to fetch cached validation errors on mount.
 * @param {string}  props.scannableUrlsRestPath       The REST path for interacting with the scannable URL resources.
 * @param {string}  props.validateNonce               The AMP validate nonce.
 * @param {string}  props.validateQueryVar            The AMP validate query variable name.
 */
export function SiteScanContextProvider( {
	children,
	fetchCachedValidationErrors = false,
	scannableUrlsRestPath,
	validateNonce,
	validateQueryVar,
} ) {
	const { setAsyncError } = useAsyncError();
	const [ state, dispatch ] = useReducer( siteScanReducer, initialState );
	const {
		cache,
		currentlyScannedUrlIndex,
		pluginIssues,
		scannableUrls,
		stale,
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

	const startSiteScan = useCallback( ( args = {} ) => {
		if ( status === STATUS_READY ) {
			dispatch( {
				type: ACTION_START_SITE_SCAN,
				cache: args?.cache,
			} );
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
				const fields = [ 'url', 'amp_url', 'type', 'label' ];
				const response = await apiFetch( {
					path: addQueryArgs( scannableUrlsRestPath, {
						_fields: fetchCachedValidationErrors ? [ ...fields, 'validation_errors', 'stale' ] : fields,
					} ),
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
	}, [ fetchCachedValidationErrors, scannableUrlsRestPath, setAsyncError, status ] );

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
							cache,
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

				dispatch( {
					type: ACTION_SCAN_RECEIVE_ISSUES,
					validationResults: validationResults.results,
				} );

				dispatch( { type: ACTION_SCAN_NEXT_URL } );
			} catch ( e ) {
				setAsyncError( e );
			}
		} )();
	}, [ cache, currentlyScannedUrlIndex, scannableUrls, setAsyncError, status, validateNonce, validateQueryVar ] );

	return (
		<SiteScan.Provider
			value={ {
				cancelSiteScan,
				currentlyScannedUrlIndex,
				isBusy: [ STATUS_IDLE, STATUS_IN_PROGRESS ].includes( status ),
				isComplete: status === STATUS_COMPLETE,
				isInitializing: [ STATUS_REQUEST_SCANNABLE_URLS, STATUS_FETCHING_SCANNABLE_URLS ].includes( status ),
				isReady: status === STATUS_READY,
				stale,
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
	fetchCachedValidationErrors: PropTypes.bool,
	scannableUrlsRestPath: PropTypes.string,
	validateNonce: PropTypes.string,
	validateQueryVar: PropTypes.string,
};
