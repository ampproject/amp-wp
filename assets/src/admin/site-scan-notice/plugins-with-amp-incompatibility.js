/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';
import { _n, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Plugins } from '../../components/plugins-context-provider';
import { getPluginSlugFromFile } from '../../common/helpers/get-plugin-slug-from-file';

/**
 * Render a DETAILS element for each plugin causing AMP incompatibilities.
 *
 * @param {Object} props                               Component props.
 * @param {Array}  props.pluginsWithAmpIncompatibility Array of plugins with AMP incompatibilities.
 */
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

	return (
		<>
			<p>
				{ _n(
					'AMP compatibility issues discovered with the following plugin:',
					'AMP compatibility issues discovered with the following plugins:',
					pluginsWithAmpIncompatibility.length,
					'amp',
				) }
			</p>
			{ pluginsWithAmpIncompatibility.map( ( pluginWithAmpIncompatibility ) => (
				<details
					key={ pluginWithAmpIncompatibility.slug }
					className="amp-site-scan-notice__plugin-details"
				>
					<summary
						className="amp-site-scan-notice__plugin-summary"
						dangerouslySetInnerHTML={ {
							__html: sprintf(
								/* translators: 1: plugin name; 2: number of URLs with AMP validation issues. */
								_n(
									'<b>%1$s</b> on %2$d URL',
									'<b>%1$s</b> on %2$d URLs',
									pluginWithAmpIncompatibility.urls.length,
									'amp',
								),
								pluginNames[ pluginWithAmpIncompatibility.slug ],
								pluginWithAmpIncompatibility.urls.length,
							),
						} }
					/>
					<ul className="amp-site-scan-notice__urls-list">
						{ pluginWithAmpIncompatibility.urls.map( ( url ) => (
							<li key={ url }>
								<a href={ url } target="_blank" rel="noopener noreferrer">
									{ url }
								</a>
							</li>
						) ) }
					</ul>
				</details>
			) ) }
		</>
	);
}

PluginsWithAmpIncompatibility.propTypes = {
	pluginsWithAmpIncompatibility: PropTypes.arrayOf(
		PropTypes.shape( {
			slug: PropTypes.string,
			urls: PropTypes.arrayOf( PropTypes.string ),
		} ),
	),
};
