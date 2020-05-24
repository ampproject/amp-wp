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

	const getCachedData = useCallback( ( key ) => ( cache || {} )[ key ], [ cache ] );

	const cacheData = useCallback( ( key, data ) => {
		setCache( { ...( cache || {} ), [ key ]: data } );
	}, [ cache, setCache ] );

	return (
		<Cache.Provider
			value={
				{
					cacheData,
					getCachedData,
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
