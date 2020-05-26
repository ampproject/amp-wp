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

	/**
	 * Gets an item from the cache.
	 *
	 * @param {string} key Cache key.
	 */
	const cacheGet = useCallback( ( key ) => ( cache || {} )[ key ], [ cache ] );

	/**
	 * Sets data in the cache.
	 *
	 * @param {string} key Cache key.
	 * @param {any} data Any data.
	 */
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
