/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';
import { _n, sprintf } from '@wordpress/i18n';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { Plugins } from '../../components/plugins-context-provider';
import { getPluginSlugFromFile } from '../../common/helpers/get-plugin-slug-from-file';

export function PluginsWithAmpIncompatibility( { pluginsWithAmpIncompatibility } ) {
	const { fetchingPlugins, plugins } = useContext( Plugins );

	if ( fetchingPlugins ) {
		return null;
	}

	const pluginNames = Object.values( plugins ).reduce( ( accumulatedPluginNames, plugin ) => {
		return {
			...accumulatedPluginNames,
			[ getPluginSlugFromFile( plugin.plugin ) ]: plugin.name,
		};
	}, {} );

	return pluginsWithAmpIncompatibility.map( ( pluginWithAmpIncompatibility ) => (
		<details
			key={ pluginWithAmpIncompatibility.slug }
			className="amp-site-scan-notice__plugin-details"
		>
			<summary className="amp-site-scan-notice__plugin-summary">
				{ sprintf(
					/* translators: 1: plugin name; 2: number of URLs with validation issues. */
					_n(
						'Validation issues caused by %1$s in %2$d URL',
						'Validation issues caused by %1$s in %2$d URLs',
						pluginWithAmpIncompatibility.urls.length,
						'amp',
					),
					pluginNames[ pluginWithAmpIncompatibility.slug ],
					pluginWithAmpIncompatibility.urls.length,
				) }
			</summary>
			<ul className="amp-site-scan-notice__urls-list">
				{ pluginWithAmpIncompatibility.urls.map( ( url ) => (
					<li key={ url }>
						<a href={ url }>
							{ url }
						</a>
					</li>
				) ) }
			</ul>
		</details>
	) );
}

PluginsWithAmpIncompatibility.propTypes = {
	pluginsWithAmpIncompatibility: PropTypes.arrayOf(
		PropTypes.shape( {
			slug: PropTypes.string,
			urls: PropTypes.arrayOf( PropTypes.string ),
		} ),
	),
};
