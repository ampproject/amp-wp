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
const ACTION_SCANNABLE_URLS_REQUEST = 'ACTION_SCANNABLE_URLS_REQUEST';
const ACTION_SCANNABLE_URLS_FETCH = 'ACTION_SCANNABLE_URLS_FETCH';
const ACTION_SCANNABLE_URLS_RECEIVE = 'ACTION_SCANNABLE_URLS_RECEIVE';
const ACTION_SCAN_INITIALIZE = 'ACTION_SCAN_INITIALIZE';
const ACTION_SCAN_VALIDATE_URL = 'ACTION_SCAN_VALIDATE_URL';
const ACTION_SCAN_RECEIVE_VALIDATION_ERRORS = 'ACTION_SCAN_RECEIVE_VALIDATION_ERRORS';
const ACTION_SCAN_NEXT_URL = 'ACTION_SCAN_NEXT_URL';
const ACTION_SCAN_CANCEL = 'ACTION_SCAN_CANCEL';

/**
 * Site Scan Statuses.
 */
const STATUS_REQUEST_SCANNABLE_URLS = 'STATUS_REQUEST_SCANNABLE_URLS';
const STATUS_FETCHING_SCANNABLE_URLS = 'STATUS_FETCHING_SCANNABLE_URLS';
const STATUS_READY = 'STATUS_READY';
const STATUS_IDLE = 'STATUS_IDLE';
const STATUS_IN_PROGRESS = 'STATUS_IN_PROGRESS';
const STATUS_COMPLETED = 'STATUS_COMPLETED';
const STATUS_FAILED = 'STATUS_FAILED';
const STATUS_CANCELLED = 'STATUS_CANCELLED';

/**
 * Initial Site Scan state.
 *
 * @type {Object}
 */
const INITIAL_STATE = {
	cache: false,
	currentlyScannedUrlIndex: 0,
	scannableUrls: [],
	status: '',
};

/**
 * Site Scan Reducer.
 *
 * @param {Object} state  Current state.
 * @param {Object} action Action to call.
 * @return {Object} New state.
 */
function siteScanReducer( state, action ) {
	switch ( action.type ) {
		case ACTION_SCANNABLE_URLS_REQUEST: {
			return {
				...state,
				status: STATUS_REQUEST_SCANNABLE_URLS,
				currentlyScannedUrlIndex: INITIAL_STATE.currentlyScannedUrlIndex,
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
				cache: action.cache,
				currentlyScannedUrlIndex: INITIAL_STATE.currentlyScannedUrlIndex,
			};
		}
		case ACTION_SCAN_VALIDATE_URL: {
			return {
				...state,
				status: STATUS_IN_PROGRESS,
			};
		}
		case ACTION_SCAN_RECEIVE_VALIDATION_ERRORS: {
			return {
				...state,
				scannableUrls: [
					...state.scannableUrls.slice( 0, action.scannedUrlIndex ),
					{
						...state.scannableUrls[ action.scannedUrlIndex ],
						stale: false,
						error: action.error ?? false,
						validated_url_post: action.error ? {} : action.validatedUrlPost,
						validation_errors: action.error ? [] : action.validationErrors,
					},
					...state.scannableUrls.slice( action.scannedUrlIndex + 1 ),
				],
			};
		}
		case ACTION_SCAN_NEXT_URL: {
			if ( state.status === STATUS_CANCELLED ) {
				return state;
			}

			if ( state.currentlyScannedUrlIndex < state.scannableUrls.length - 1 ) {
				return {
					...state,
					status: STATUS_IDLE,
					currentlyScannedUrlIndex: state.currentlyScannedUrlIndex + 1,
				};
			}

			const hasFailed = state.scannableUrls.every( ( scannableUrl ) => Boolean( scannableUrl.error ) );

			return {
				...state,
				status: hasFailed ? STATUS_FAILED : STATUS_COMPLETED,
			};
		}
		case ACTION_SCAN_CANCEL: {
			if ( ! [ STATUS_IDLE, STATUS_IN_PROGRESS ].includes( state.status ) ) {
				return state;
			}

			return {
				...state,
				status: STATUS_CANCELLED,
				currentlyScannedUrlIndex: INITIAL_STATE.currentlyScannedUrlIndex,
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
 * @param {boolean} props.ampFirst                    Whether scanning should be done with Standard mode being forced.
 * @param {?any}    props.children                    Component children.
 * @param {boolean} props.fetchCachedValidationErrors Whether to fetch cached validation errors on mount.
 * @param {string}  props.scannableUrlsRestPath       The REST path for interacting with the scannable URL resources.
 * @param {string}  props.validateNonce               The AMP validate nonce.
 */
export function SiteScanContextProvider( {
	ampFirst = false,
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
	} = useContext( Options );
	const { setAsyncError } = useAsyncError();
	const [ state, dispatch ] = useReducer( siteScanReducer, INITIAL_STATE );
	const {
		cache,
		currentlyScannedUrlIndex,
		scannableUrls,
		status,
	} = state;
	const urlType = ampFirst || themeSupport === STANDARD ? 'url' : 'amp_url';
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
	useEffect( () => {
		if ( status ) {
			return;
		}

		if ( ! validateNonce ) {
			throw new Error( 'Invalid site scan configuration' );
		}

		dispatch( { type: ACTION_SCANNABLE_URLS_REQUEST } );
	}, [ status, validateNonce ] );

	/**
	 * This component sets state inside async functions. Use this ref to prevent
	 * state updates after unmount.
	 */
	const hasUnmounted = useRef( false );
	useEffect( () => () => {
		hasUnmounted.current = true;
	}, [] );

	const startSiteScan = useCallback( ( args = {} ) => {
		dispatch( {
			type: ACTION_SCAN_INITIALIZE,
			cache: args?.cache,
		} );
	}, [] );

	const cancelSiteScan = useCallback( () => {
		dispatch( { type: ACTION_SCAN_CANCEL } );
	}, [] );

	/**
	 * Cancel scan and invalidate current results whenever options change.
	 */
	useEffect( () => {
		if ( stale && [ STATUS_IN_PROGRESS, STATUS_IDLE ].includes( status ) ) {
			dispatch( { type: ACTION_SCAN_CANCEL } );
		}
	}, [ stale, status ] );

	/**
	 * Monitor changes to the options.
	 */
	const previousDidSaveOptions = usePrevious( didSaveOptions );
	useEffect( () => {
		if ( ! previousDidSaveOptions && didSaveOptions ) {
			dispatch( { type: ACTION_SCANNABLE_URLS_REQUEST } );
		}
	}, [ didSaveOptions, previousDidSaveOptions ] );

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
		( async () => {
			if ( status !== STATUS_IDLE ) {
				return;
			}

			dispatch( { type: ACTION_SCAN_VALIDATE_URL } );

			try {
				const url = scannableUrls[ currentlyScannedUrlIndex ][ urlType ];
				const args = {
					'amp-first': ampFirst || undefined,
					amp_validate: {
						cache: cache || undefined,
						nonce: validateNonce,
						omit_stylesheets: true,
						cache_bust: Math.random(),
					},
				};

				const response = await fetch( addQueryArgs( url, args ) );
				const data = await response.json();

				if ( true === hasUnmounted.current ) {
					return;
				}

				if ( response.ok ) {
					dispatch( {
						type: ACTION_SCAN_RECEIVE_VALIDATION_ERRORS,
						scannedUrlIndex: currentlyScannedUrlIndex,
						validatedUrlPost: data.validated_url_post,
						validationErrors: data.results.map( ( { error } ) => error ),
					} );
				} else {
					dispatch( {
						type: ACTION_SCAN_RECEIVE_VALIDATION_ERRORS,
						scannedUrlIndex: currentlyScannedUrlIndex,
						error: data?.code || true,
					} );
				}
			} catch ( e ) {
				dispatch( {
					type: ACTION_SCAN_RECEIVE_VALIDATION_ERRORS,
					scannedUrlIndex: currentlyScannedUrlIndex,
					error: true,
				} );
			}

			dispatch( { type: ACTION_SCAN_NEXT_URL } );
		} )();
	}, [ ampFirst, cache, currentlyScannedUrlIndex, scannableUrls, setAsyncError, status, urlType, validateNonce ] );

	return (
		<SiteScan.Provider
			value={ {
				cancelSiteScan,
				currentlyScannedUrlIndex,
				hasSiteScanResults,
				isBusy: [ STATUS_IDLE, STATUS_IN_PROGRESS ].includes( status ),
				isCancelled: status === STATUS_CANCELLED,
				isCompleted: status === STATUS_COMPLETED,
				isFailed: status === STATUS_FAILED,
				isInitializing: [ STATUS_REQUEST_SCANNABLE_URLS, STATUS_FETCHING_SCANNABLE_URLS ].includes( status ),
				isReady: status === STATUS_READY,
				isSiteScannable: scannableUrls.length > 0,
				pluginsWithAmpIncompatibility,
				previewPermalink,
				scannableUrls,
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
	ampFirst: PropTypes.bool,
	children: PropTypes.any,
	fetchCachedValidationErrors: PropTypes.bool,
	scannableUrlsRestPath: PropTypes.string,
	validateNonce: PropTypes.string,
};
