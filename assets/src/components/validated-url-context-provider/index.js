/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import {
	createContext,
	useContext,
	useEffect,
	useRef,
	useState,
} from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import { ErrorContext } from '../error-context-provider';
import { useAsyncError } from '../../utils/use-async-error';
import calculateStylesheetStats from './calculate-stylesheet-stats';

export const ValidatedUrl = createContext();

export const STYLESHEETS_BUDGET_STATUS_VALID = 'valid';
export const STYLESHEETS_BUDGET_STATUS_WARNING = 'warning';
export const STYLESHEETS_BUDGET_STATUS_EXCEEDED = 'exceeded';

/**
 * Validated URL data context provider.
 *
 * @param {Object} props Component props.
 * @param {any} props.children Component children.
 * @param {number} props.cssBudgetBytes CSS budget value in bytes.
 * @param {number} props.cssBudgetWarningPercentage CSS budget warning level percentage.
 * @param {boolean} props.hasErrorBoundary Whether the component is wrapped in an error boundary.
 * @param {number} props.postId Validated URL post ID.
 * @param {string} props.validatedUrlsRestPath REST endpoint to retrieve validated URL data.
 */
export function ValidatedUrlContextProvider( {
	children,
	cssBudgetBytes,
	cssBudgetWarningPercentage,
	hasErrorBoundary = false,
	postId,
	validatedUrlsRestPath,
} ) {
	const [ validatedUrl, setValidatedUrl ] = useState( {} );
	const [ stylesheetStats, setStylesheetStats ] = useState();
	const [ fetchingValidatedUrl, setFetchingValidatedUrl ] = useState( null );

	const { error, setError } = useContext( ErrorContext );
	const { setAsyncError } = useAsyncError();

	/**
	 * This component sets state inside async functions.
	 * Use this ref to prevent state updates after unmount.
	 */
	const hasUnmounted = useRef( false );
	useEffect( () => () => {
		hasUnmounted.current = true;
	}, [] );

	/**
	 * Fetches validated URL data.
	 */
	useEffect( () => {
		if ( error || Object.keys( validatedUrl ).length || fetchingValidatedUrl ) {
			return;
		}

		( async () => {
			setFetchingValidatedUrl( true );

			try {
				const fetchedValidatedUrl = await apiFetch( {
					path: `${ validatedUrlsRestPath }/${ postId }`,
				} );

				if ( hasUnmounted.current === true ) {
					return;
				}

				setValidatedUrl( fetchedValidatedUrl );
			} catch ( e ) {
				if ( hasUnmounted.current === true ) {
					return;
				}

				setError( e );

				if ( hasErrorBoundary ) {
					setAsyncError( e );
				}

				return;
			}

			setFetchingValidatedUrl( false );
		} )();
	}, [ error, fetchingValidatedUrl, hasErrorBoundary, postId, setAsyncError, setError, validatedUrl, validatedUrlsRestPath ] );

	/**
	 * Calculate stylesheet stats.
	 */
	useEffect( () => {
		if (
			! validatedUrl?.stylesheets ||
			validatedUrl.stylesheets?.errors ||
			! Array.isArray( validatedUrl.stylesheets ) ||
			validatedUrl.stylesheets.length === 0
		) {
			return;
		}

		setStylesheetStats( calculateStylesheetStats( [ ...validatedUrl.stylesheets ], cssBudgetBytes, cssBudgetWarningPercentage ) );
	}, [ cssBudgetBytes, cssBudgetWarningPercentage, validatedUrl.stylesheets ] );

	return (
		<ValidatedUrl.Provider
			value={ {
				fetchingValidatedUrl,
				stylesheetStats,
				validatedUrl,
			} }
		>
			{ children }
		</ValidatedUrl.Provider>
	);
}
ValidatedUrlContextProvider.propTypes = {
	children: PropTypes.any,
	cssBudgetBytes: PropTypes.number,
	cssBudgetWarningPercentage: PropTypes.number,
	hasErrorBoundary: PropTypes.bool,
	postId: PropTypes.number,
	validatedUrlsRestPath: PropTypes.string,
};
