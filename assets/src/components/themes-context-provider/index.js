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

export const Themes = createContext();

/**
 * Themes context provider.
 *
 * @param {Object}  props                  Component props.
 * @param {any}     props.children         Component children.
 * @param {boolean} props.hasErrorBoundary Whether the component is wrapped in an error boundary.
 */
export function ThemesContextProvider( {
	children,
	hasErrorBoundary = false,
} ) {
	const [ themes, setThemes ] = useState( [] );
	const [ fetchingThemes, setFetchingThemes ] = useState( null );

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
	 * Fetches the themes data.
	 */
	useEffect( () => {
		if ( error || themes.length > 0 || fetchingThemes ) {
			return;
		}

		( async () => {
			setFetchingThemes( true );

			try {
				const fetchedThemes = await apiFetch( {
					path: '/wp/v2/themes',
				} );

				if ( hasUnmounted.current === true ) {
					return;
				}

				setThemes( fetchedThemes );
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

			setFetchingThemes( false );
		} )();
	}, [ error, fetchingThemes, hasErrorBoundary, themes, setAsyncError, setError ] );

	return (
		<Themes.Provider
			value={ {
				fetchingThemes,
				themes,
			} }
		>
			{ children }
		</Themes.Provider>
	);
}
ThemesContextProvider.propTypes = {
	children: PropTypes.any,
	hasErrorBoundary: PropTypes.bool,
};
