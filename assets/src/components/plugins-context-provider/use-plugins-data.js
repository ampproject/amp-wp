/**
 * WordPress dependencies
 */
import { useContext, useEffect, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

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

	const getPluginNameBySlug = ( slug ) => nameBySlug[ slug ] ??
		sprintf(
			/* translators: Plugin slug. */
			__( 'Plugin: %s', 'amp' ),
			slug,
		);

	return {
		getPluginNameBySlug,
		plugins,
	};
}
