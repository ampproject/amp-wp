/**
 * WordPress dependencies
 */
import { createContext, useState, useCallback } from '@wordpress/element';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

export const Cache = createContext();

/**
 * Context provider for caching data. Allows data to persist between screen navigations.
 *
 * @param {Object} props Component props.
 * @param {?any} props.children Component children.
 */
export function CacheContextProvider( { children } ) {
	const [ cache, setCache ] = useState( () => {} );

	const cacheGet = useCallback( ( key ) => ( cache || {} )[ key ], [ cache ] );

	const cacheSet = useCallback( ( key, data ) => {
		setCache( { ...( cache || {} ), [ key ]: data } );
	}, [ cache, setCache ] );

	return (
		<Cache.Provider
			value={
				{
					cacheSet,
					cacheGet,
				}
			}
		>
			{ children }
		</Cache.Provider>
	);
}

CacheContextProvider.propTypes = {
	children: PropTypes.any,
};
