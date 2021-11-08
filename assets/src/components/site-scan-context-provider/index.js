/**
 * WordPress dependencies
 */
import {
	createContext,
	useCallback,
	useContext,
	useEffect,
	useMemo,
	useReducer,
	useRef,
	useState,
} from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { usePrevious } from '@wordpress/compose';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { STANDARD } from '../../common/constants';
import { useAsyncError } from '../../utils/use-async-error';
import { Options } from '../options-context-provider';
import { getSlugsFromValidationResults } from './get-slugs-from-validation-results';

export const SiteScan = createContext();

/**
 * Site Scan Actions.
 */
export const ACTION_SCANNABLE_URLS_REQUEST = 'ACTION_SCANNABLE_URLS_REQUEST';
export const ACTION_SCANNABLE_URLS_FETCH = 'ACTION_SCANNABLE_URLS_FETCH';
export const ACTION_SCANNABLE_URLS_RECEIVE = 'ACTION_SCANNABLE_URLS_RECEIVE';
export const ACTION_SCAN_INITIALIZE = 'ACTION_SCAN_INITIALIZE';
export const ACTION_SCAN_URL = 'ACTION_SCAN_URL';
export const ACTION_SCAN_RECEIVE_RESULTS = 'ACTION_SCAN_RECEIVE_RESULTS';
export const ACTION_SCAN_COMPLETE = 'ACTION_SCAN_COMPLETE';
export const ACTION_SCAN_SUCCESS = 'ACTION_SCAN_SUCCESS';
export const ACTION_SCAN_CANCEL = 'ACTION_SCAN_CANCEL';

/**
 * Site Scan Statuses.
 */
export const STATUS_REQUEST_SCANNABLE_URLS = 'STATUS_REQUEST_SCANNABLE_URLS';
export const STATUS_FETCHING_SCANNABLE_URLS = 'STATUS_FETCHING_SCANNABLE_URLS';
export const STATUS_REFETCHING_PLUGIN_SUPPRESSION = 'STATUS_REFETCHING_PLUGIN_SUPPRESSION';
export const STATUS_READY = 'STATUS_READY';
export const STATUS_IDLE = 'STATUS_IDLE';
export const STATUS_IN_PROGRESS = 'STATUS_IN_PROGRESS';
export const STATUS_COMPLETED = 'STATUS_COMPLETED';
export const STATUS_FAILED = 'STATUS_FAILED';
export const STATUS_CANCELLED = 'STATUS_CANCELLED';

/**
 * Initial Site Scan state.
 *
 * @type {Object}
 */
const INITIAL_STATE = {
	currentlyScannedUrlIndexes: [],
	forceStandardMode: false,
	scannableUrls: [],
	status: '',
	urlIndexesPendingScan: [],
};

/**
 * The maximum number of validation requests that can be issued concurrently.
 *
 * @type {number}
 */
const CONCURRENT_VALIDATION_REQUESTS_MAX_COUNT = 3;

/**
 * The number of milliseconds to wait between subsequent validation requests.
 *
 * @type {number}
 */
const CONCURRENT_VALIDATION_REQUESTS_WAIT_MS = 500;

/**
 * Site Scan Reducer.
 *
 * @param {Object} state  Current state.
 * @param {Object} action Action to call.
 * @return {Object} New state.
 */
export function siteScanReducer( state, action ) {
	switch ( action.type ) {
		case ACTION_SCANNABLE_URLS_REQUEST: {
			return {
				...state,
				status: STATUS_REQUEST_SCANNABLE_URLS,
				forceStandardMode: action?.forceStandardMode ?? false,
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
				status: action.scannableUrls?.length > 0 ? STATUS_READY : STATUS_COMPLETED,
				scannableUrls: action.scannableUrls,
			};
		}
		case ACTION_SCAN_INITIALIZE: {
			if ( ! [ STATUS_READY, STATUS_COMPLETED, STATUS_FAILED, STATUS_CANCELLED ].includes( state.status ) ) {
				return state;
			}

			return {
				...state,
				status: STATUS_IDLE,
				currentlyScannedUrlIndexes: [],
				urlIndexesPendingScan: state.scannableUrls.map( ( url, index ) => index ),
			};
		}
		case ACTION_SCAN_URL: {
			if ( ! [ STATUS_IDLE, STATUS_IN_PROGRESS ].includes( state.status ) ) {
				return state;
			}

			return {
				...state,
				status: STATUS_IN_PROGRESS,
				currentlyScannedUrlIndexes: [
					...state.currentlyScannedUrlIndexes,
					action.currentlyScannedUrlIndex,
				],
				urlIndexesPendingScan: state.urlIndexesPendingScan.filter( ( index ) => index !== action.currentlyScannedUrlIndex ),
			};
		}
		case ACTION_SCAN_RECEIVE_RESULTS: {
			if ( ! [ STATUS_IDLE, STATUS_IN_PROGRESS ].includes( state.status ) ) {
				return state;
			}

			return {
				...state,
				status: STATUS_IDLE,
				currentlyScannedUrlIndexes: state.currentlyScannedUrlIndexes.filter( ( index ) => index !== action.currentlyScannedUrlIndex ),
				scannableUrls: [
					...state.scannableUrls.slice( 0, action.currentlyScannedUrlIndex ),
					{
						...state.scannableUrls[ action.currentlyScannedUrlIndex ],
						stale: false,
						error: action.error ?? false,
						validated_url_post: action.error ? {} : action.validatedUrlPost,
						validation_errors: action.error ? [] : action.validationErrors,
					},
					...state.scannableUrls.slice( action.currentlyScannedUrlIndex + 1 ),
				],
			};
		}
		case ACTION_SCAN_COMPLETE: {
			const hasFailed = state.scannableUrls.every( ( scannableUrl ) => Boolean( scannableUrl.error ) );

			return {
				...state,
				status: hasFailed ? STATUS_FAILED : STATUS_REFETCHING_PLUGIN_SUPPRESSION,
			};
		}
		case ACTION_SCAN_SUCCESS: {
			return {
				...state,
				status: STATUS_COMPLETED,
			};
		}
		case ACTION_SCAN_CANCEL: {
			if ( ! [ STATUS_IDLE, STATUS_IN_PROGRESS ].includes( state.status ) ) {
				return state;
			}

			return {
				...state,
				status: STATUS_CANCELLED,
				currentlyScannedUrlIndexes: [],
				urlIndexesPendingScan: [],
			};
		}
		default: {
			throw new Error( `Unhandled action type: ${ action.type }` );
		}
	}
}

/**
 * Context provider for site scanning.
 *
 * @param {Object}  props                             Component props.
 * @param {?any}    props.children                    Component children.
 * @param {boolean} props.fetchCachedValidationErrors Whether to fetch cached validation errors on mount.
 * @param {string}  props.scannableUrlsRestPath       The REST path for interacting with the scannable URL resources.
 * @param {string}  props.validateNonce               The AMP validate nonce.
 */
export function SiteScanContextProvider( {
	children,
	fetchCachedValidationErrors = false,
	scannableUrlsRestPath,
	validateNonce,
} ) {
	const {
		didSaveOptions,
		originalOptions: {
			theme_support: themeSupport,
		},
		refetchPluginSuppression,
	} = useContext( Options );
	const { setAsyncError } = useAsyncError();
	const [ state, dispatch ] = useReducer( siteScanReducer, INITIAL_STATE );
	const {
		currentlyScannedUrlIndexes,
		forceStandardMode,
		scannableUrls,
		urlIndexesPendingScan,
		status,
	} = state;
	const urlType = forceStandardMode || themeSupport === STANDARD ? 'url' : 'amp_url';
	const previewPermalink = scannableUrls?.[ 0 ]?.[ urlType ] ?? '';

	/**
	 * Memoize properties.
	 */
	const {
		hasSiteScanResults,
		pluginsWithAmpIncompatibility,
		stale,
		themesWithAmpIncompatibility,
	} = useMemo( () => {
		// Skip if the scan is in progress.
		if ( ! [ STATUS_READY, STATUS_COMPLETED ].includes( status ) ) {
			return {
				hasSiteScanResults: false,
				pluginsWithAmpIncompatibility: [],
				stale: false,
				themesWithAmpIncompatibility: [],
			};
		}

		const validationErrors = scannableUrls.reduce( ( accumulatedValidationErrors, scannableUrl ) => [ ...accumulatedValidationErrors, ...scannableUrl?.validation_errors ?? [] ], [] );
		const slugs = getSlugsFromValidationResults( validationErrors );

		return {
			hasSiteScanResults: scannableUrls.some( ( scannableUrl ) => Boolean( scannableUrl?.validation_errors ) ),
			pluginsWithAmpIncompatibility: slugs.plugins,
			stale: scannableUrls.some( ( scannableUrl ) => scannableUrl?.stale === true ),
			themesWithAmpIncompatibility: slugs.themes,
		};
	}, [ scannableUrls, status ] );

	/**
	 * Preflight check.
	 */
	if ( ! validateNonce ) {
		throw new Error( 'Invalid site scan configuration' );
	}

	/**
	 * This component sets state inside async functions. Use this ref to prevent
	 * state updates after unmount.
	 */
	const hasUnmounted = useRef( false );
	useEffect( () => () => {
		hasUnmounted.current = true;
	}, [] );

	const fetchScannableUrls = useCallback( ( args = {} ) => {
		dispatch( {
			type: ACTION_SCANNABLE_URLS_REQUEST,
			forceStandardMode: args?.forceStandardMode,
		} );
	}, [] );

	const startSiteScan = useCallback( () => {
		dispatch( { type: ACTION_SCAN_INITIALIZE } );
	}, [] );

	const cancelSiteScan = useCallback( () => {
		dispatch( { type: ACTION_SCAN_CANCEL } );
	}, [] );

	/**
	 * Whenever options change, cancel the current scan (if in progress) and
	 * refetch the scannable URLs.
	 */
	const previousDidSaveOptions = usePrevious( didSaveOptions );
	useEffect( () => {
		if ( ! previousDidSaveOptions && didSaveOptions ) {
			cancelSiteScan();
			fetchScannableUrls();
		}
	}, [ cancelSiteScan, didSaveOptions, fetchScannableUrls, previousDidSaveOptions ] );

	/**
	 * Delay concurrent validation requests.
	 */
	const [ shouldDelayValidationRequest, setShouldDelayValidationRequest ] = useState( false );
	useEffect( () => {
		let clearTimeout = () => {};

		if ( shouldDelayValidationRequest ) {
			( async () => {
				await new Promise( ( resolve ) => {
					clearTimeout = setTimeout( resolve, CONCURRENT_VALIDATION_REQUESTS_WAIT_MS );
				} );
				setShouldDelayValidationRequest( false );
			} )();
		}

		return clearTimeout;
	}, [ shouldDelayValidationRequest ] );

	/**
	 * Once the site scan is complete, refetch the plugin suppression data so
	 * that the suppressed table is updated with the latest validation errors.
	 */
	useEffect( () => {
		if ( status === STATUS_REFETCHING_PLUGIN_SUPPRESSION ) {
			refetchPluginSuppression();
			dispatch( { type: ACTION_SCAN_SUCCESS } );
		}
	}, [ refetchPluginSuppression, status ] );

	/**
	 * Fetch scannable URLs from the REST endpoint.
	 */
	useEffect( () => {
		( async () => {
			if ( status !== STATUS_REQUEST_SCANNABLE_URLS ) {
				return;
			}

			dispatch( { type: ACTION_SCANNABLE_URLS_FETCH } );

			try {
				const fields = [ 'url', 'amp_url', 'type', 'label' ];
				const response = await apiFetch( {
					path: addQueryArgs( scannableUrlsRestPath, {
						_fields: fetchCachedValidationErrors ? [ ...fields, 'validation_errors', 'stale' ] : fields,
						force_standard_mode: forceStandardMode ? 1 : undefined,
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
	}, [ fetchCachedValidationErrors, forceStandardMode, scannableUrlsRestPath, setAsyncError, status ] );

	/**
	 * Scan site URLs sequentially.
	 */
	useEffect( () => {
		if ( ! [ STATUS_IDLE, STATUS_IN_PROGRESS ].includes( status ) ) {
			return;
		}

		/**
		 * If there are no more URLs to scan and no URLs are scanned at the
		 * moment, finish the site scan.
		 */
		if ( urlIndexesPendingScan.length === 0 ) {
			if ( currentlyScannedUrlIndexes.length === 0 ) {
				dispatch( { type: ACTION_SCAN_COMPLETE } );
			}

			return;
		}

		if ( shouldDelayValidationRequest || currentlyScannedUrlIndexes.length >= CONCURRENT_VALIDATION_REQUESTS_MAX_COUNT ) {
			return;
		}

		setShouldDelayValidationRequest( true );

		const currentlyScannedUrlIndex = urlIndexesPendingScan.shift();

		dispatch( {
			type: ACTION_SCAN_URL,
			currentlyScannedUrlIndex,
		} );

		( async () => {
			const results = {};

			try {
				const scannableUrl = scannableUrls[ currentlyScannedUrlIndex ];
				const url = scannableUrl[ urlType ];
				const args = {
					amp_validate: {
						cache: true,
						cache_bust: Math.random(),
						force_standard_mode: forceStandardMode || undefined,
						nonce: validateNonce,
						omit_stylesheets: true,
					},
				};

				const response = await fetch( addQueryArgs( url, args ) );
				const data = await response.json();

				if ( true === hasUnmounted.current ) {
					return;
				}

				if ( response.ok ) {
					results.validatedUrlPost = data.validated_url_post;
					results.validationErrors = data.results.map( ( { error } ) => error );
				} else {
					results.error = data?.code || true;
				}
			} catch ( e ) {
				results.error = true;
			}

			dispatch( {
				type: ACTION_SCAN_RECEIVE_RESULTS,
				currentlyScannedUrlIndex,
				...results,
			} );

			setShouldDelayValidationRequest( false );
		} )();
	}, [ currentlyScannedUrlIndexes.length, forceStandardMode, scannableUrls, shouldDelayValidationRequest, status, urlIndexesPendingScan, urlType, validateNonce ] );

	return (
		<SiteScan.Provider
			value={ {
				cancelSiteScan,
				fetchScannableUrls,
				hasSiteScanResults,
				isBusy: [ STATUS_IDLE, STATUS_IN_PROGRESS ].includes( status ),
				isCancelled: status === STATUS_CANCELLED,
				isCompleted: [ STATUS_REFETCHING_PLUGIN_SUPPRESSION, STATUS_COMPLETED ].includes( status ),
				isFailed: status === STATUS_FAILED,
				isFetchingScannableUrls: [ STATUS_REQUEST_SCANNABLE_URLS, STATUS_FETCHING_SCANNABLE_URLS ].includes( status ),
				isReady: status === STATUS_READY,
				isSiteScannable: scannableUrls.length > 0,
				pluginsWithAmpIncompatibility,
				previewPermalink,
				scannableUrls,
				scannedUrlsMaxIndex: Math.min( scannableUrls.length, ...urlIndexesPendingScan ) - 1,
				stale,
				startSiteScan,
				themesWithAmpIncompatibility,
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
};
