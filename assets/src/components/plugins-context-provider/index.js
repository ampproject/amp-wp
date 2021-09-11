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

export const Plugins = createContext();

/**
 * Plugins context provider.
 *
 * @param {Object} props Component props.
 * @param {any} props.children Component children.
 * @param {boolean} props.hasErrorBoundary Whether the component is wrapped in an error boundary.
 */
export function PluginsContextProvider( {
	children,
	hasErrorBoundary = false,
} ) {
	const [ plugins, setPlugins ] = useState();
	const [ fetchingPlugins, setFetchingPlugins ] = useState( null );

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
		if ( error || plugins || fetchingPlugins ) {
			return;
		}

		( async () => {
			setFetchingPlugins( true );

			try {
				const fetchedPlugins = await apiFetch( {
					path: '/wp/v2/plugins',
				} );

				if ( hasUnmounted.current === true ) {
					return;
				}

				setPlugins( fetchedPlugins );
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

			setFetchingPlugins( false );
		} )();
	}, [ error, fetchingPlugins, hasErrorBoundary, plugins, setAsyncError, setError ] );

	return (
		<Plugins.Provider
			value={ {
				fetchingPlugins,
				plugins,
			} }
		>
			{ children }
		</Plugins.Provider>
	);
}
PluginsContextProvider.propTypes = {
	children: PropTypes.any,
	hasErrorBoundary: PropTypes.bool,
};
