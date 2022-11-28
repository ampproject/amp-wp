/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { createContext, useEffect, useRef, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

export const Plugins = createContext();

/**
 * Plugins context provider.
 *
 * @param {Object} props          Component props.
 * @param {any}    props.children Component children.
 */
export function PluginsContextProvider( { children } ) {
	const [ plugins, setPlugins ] = useState( [] );
	const [ fetchingPlugins, setFetchingPlugins ] = useState( null );
	const [ error, setError ] = useState();

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
		if ( error || plugins.length > 0 || fetchingPlugins ) {
			return;
		}

		( async () => {
			setFetchingPlugins( true );

			try {
				const fetchedPlugins = await apiFetch( {
					path: addQueryArgs( '/wp/v2/plugins', {
						_fields: [ 'author', 'name', 'plugin', 'status', 'version' ],
					} ),
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
			}

			setFetchingPlugins( false );
		} )();
	}, [ error, fetchingPlugins, plugins ] );

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
};
