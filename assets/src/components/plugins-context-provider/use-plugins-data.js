/**
 * WordPress dependencies
 */
import { useContext, useEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Plugins } from './index';

export default function usePluginsData() {
	const { plugins, fetchingPlugins } = useContext( Plugins );
	const [ nameBySlug, setNameBySlug ] = useState( [] );

	useEffect( () => {
		if ( fetchingPlugins || ! plugins ) {
			return;
		}

		setNameBySlug( () => plugins.reduce( ( acc, plugin ) => {
			const slug = plugin.plugin.split( '/' )[ 0 ];
			return {
				...acc,
				[ slug ]: plugin.name,
			};
		}, {} ) );
	}, [ fetchingPlugins, plugins ] );

	const getPluginNameBySlug = ( slug ) => nameBySlug[ slug ] ?? slug;

	return {
		getPluginNameBySlug,
		plugins,
	};
}
