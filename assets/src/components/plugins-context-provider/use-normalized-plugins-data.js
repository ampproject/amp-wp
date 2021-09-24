/**
 * WordPress dependencies
 */
import { useContext, useEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Plugins } from './index';

export function useNormalizedPluginsData( { skipInactive = true } = {} ) {
	const { fetchingPlugins, plugins } = useContext( Plugins );
	const [ normalizedPluginsData, setNormalizedPluginsData ] = useState( [] );

	useEffect( () => {
		if ( fetchingPlugins || plugins.length === 0 ) {
			return;
		}

		setNormalizedPluginsData( () => plugins.reduce( ( acc, source ) => {
			const { status, plugin } = source;

			if ( skipInactive && status !== 'active' ) {
				return acc;
			}

			const pluginSlug = plugin.match( /^(?:[^\/]*\/)?(.*?)$/ )[ 1 ];

			if ( ! pluginSlug ) {
				return acc;
			}

			return {
				...acc,
				[ pluginSlug ]: Object.keys( source ).reduce( ( props, key ) => ( {
					...props,
					// Flatten every prop that contains a `raw` member.
					[ key ]: source[ key ]?.raw ?? source[ key ],
				} ), {} ),
			};
		}, {} ) );
	}, [ fetchingPlugins, plugins, skipInactive ] );

	return normalizedPluginsData;
}
