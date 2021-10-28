/**
 * WordPress dependencies
 */
import { useContext, useEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { getPluginSlugFromFile } from '../../common/helpers/get-plugin-slug-from-file';
import { Plugins } from './index';

export function useNormalizedPluginsData() {
	const { fetchingPlugins, plugins } = useContext( Plugins );
	const [ normalizedPluginsData, setNormalizedPluginsData ] = useState( [] );

	useEffect( () => {
		if ( fetchingPlugins || plugins.length === 0 ) {
			return;
		}

		setNormalizedPluginsData( plugins.reduce( ( accumulatedPluginsData, source ) => {
			const slug = getPluginSlugFromFile( source.plugin );

			return {
				...accumulatedPluginsData,
				[ slug ]: Object.keys( source ).reduce( ( props, key ) => ( {
					...props,
					slug,
					// Flatten every prop that contains a `raw` member.
					[ key ]: source[ key ]?.raw ?? source[ key ],
				} ), {} ),
			};
		}, {} ) );
	}, [ fetchingPlugins, plugins ] );

	return normalizedPluginsData;
}
