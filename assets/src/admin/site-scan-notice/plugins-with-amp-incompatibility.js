/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import {
	AMP_COMPATIBLE_PLUGINS_URL,
	SETTINGS_LINK,
} from 'amp-site-scan-notice'; // From WP inline script.

/**
 * WordPress dependencies
 */
import {
	createInterpolateElement,
	useContext,
	useMemo,
} from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Plugins } from '../../components/plugins-context-provider';
import { getPluginSlugFromFile } from '../../common/helpers/get-plugin-slug-from-file';
import { isExternalUrl } from '../../common/helpers/is-external-url';

// Define Plugin Suppression link.
const pluginSuppressionUrl = new URL(SETTINGS_LINK);
pluginSuppressionUrl.hash = 'plugin-suppression';

/**
 * Render a DETAILS element for each plugin causing AMP incompatibilities.
 *
 * @param {Object} props                               Component props.
 * @param {Array}  props.pluginsWithAmpIncompatibility Array of plugins with AMP incompatibilities.
 */
export function PluginsWithAmpIncompatibility({
	pluginsWithAmpIncompatibility,
}) {
	const { fetchingPlugins, plugins } = useContext(Plugins);

	const pluginNames = useMemo(
		() =>
			plugins?.reduce(
				(accumulatedPluginNames, plugin) => ({
					...accumulatedPluginNames,
					[getPluginSlugFromFile(plugin.plugin)]: plugin.name,
				}),
				{}
			),
		[plugins]
	);

	if (fetchingPlugins) {
		return null;
	}

	return (
		<>
			<p>
				{_n(
					'AMP compatibility issue(s) discovered with the following plugin:',
					'AMP compatibility issue(s) discovered with the following plugins:',
					pluginsWithAmpIncompatibility.length,
					'amp'
				)}
			</p>
			{pluginsWithAmpIncompatibility.map(
				(pluginWithAmpIncompatibility) => (
					<details
						key={pluginWithAmpIncompatibility.slug}
						className="amp-site-scan-notice__source-details"
					>
						<summary className="amp-site-scan-notice__source-summary">
							{createInterpolateElement(
								sprintf(
									/* translators: 1: plugin name; 2: number of URLs with AMP validation issues. */
									_n(
										'<b>%1$s</b> on %2$d URL',
										'<b>%1$s</b> on %2$d URLs',
										pluginWithAmpIncompatibility.urls
											.length,
										'amp'
									),
									pluginNames[
										pluginWithAmpIncompatibility.slug
									],
									pluginWithAmpIncompatibility.urls.length
								),
								{
									b: <b />,
								}
							)}
						</summary>
						<ul className="amp-site-scan-notice__urls-list">
							{pluginWithAmpIncompatibility.urls.map((url) => (
								<li key={url}>
									<a
										href={url}
										target="_blank"
										rel="noopener noreferrer"
									>
										{url}
									</a>
								</li>
							))}
						</ul>
					</details>
				)
			)}
			<div className="amp-site-scan-notice__cta">
				<a href={pluginSuppressionUrl.href} className="button">
					{__('Review Plugin Suppression', 'amp')}
				</a>
				<a
					href={AMP_COMPATIBLE_PLUGINS_URL}
					className="button"
					{...(isExternalUrl(AMP_COMPATIBLE_PLUGINS_URL)
						? { target: '_blank', rel: 'noopener noreferrer' }
						: {})}
				>
					{__('View AMP-Compatible Plugins', 'amp')}
				</a>
			</div>
		</>
	);
}

PluginsWithAmpIncompatibility.propTypes = {
	pluginsWithAmpIncompatibility: PropTypes.arrayOf(
		PropTypes.shape({
			slug: PropTypes.string,
			urls: PropTypes.arrayOf(PropTypes.string),
		})
	).isRequired,
};
