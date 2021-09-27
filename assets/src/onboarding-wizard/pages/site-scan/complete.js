/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { VALIDATED_URLS_LINK } from 'amp-settings'; // From WP inline script.

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useContext } from '@wordpress/element';
import { ExternalLink, VisuallyHidden } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { Selectable } from '../../../components/selectable';
import { SiteScan } from '../../../components/site-scan-context-provider';
import { IconLandscapeHillsCogs } from '../../../components/svg/landscape-hills-cogs';
import { IconWebsitePaintBrush } from '../../../components/svg/website-paint-brush';
import { IconLaptopPlug } from '../../../components/svg/laptop-plug';
import { Loading } from '../../../components/loading';
import { useNormalizedPluginsData } from '../../../components/plugins-context-provider/use-normalized-plugins-data';
import { useNormalizedThemesData } from '../../../components/themes-context-provider/use-normalized-themes-data';
import { SourcesList } from './sources-list';

/**
 * Screen with site scan summary.
 */
export function SiteScanComplete() {
	const { pluginIssues, themeIssues } = useContext( SiteScan );
	const hasThemeIssues = themeIssues.length > 0;
	const hasPluginIssues = pluginIssues.length > 0;

	return (
		<div className="site-scan">
			<Selectable className="site-scan__section">
				<div className="site-scan__header">
					<IconLandscapeHillsCogs />
					<h2 className="site-scan__heading">
						{ __( 'Scan complete', 'amp' ) }
					</h2>
				</div>
				<p>
					{ hasThemeIssues || hasPluginIssues
						? __( 'Site scan found issues on your site. Proceed to the next step to follow recommendations for choosing a template mode.', 'amp' )
						: __( 'Site scan found no issues on your site. Proceed to the next step to follow recommendations for choosing a template mode.', 'amp' )
					}
				</p>
			</Selectable>
			{ hasThemeIssues && (
				<Selectable className="site-scan__section site-scan__section--compact">
					<div className="site-scan__header">
						<IconWebsitePaintBrush />
						<p
							className="site-scan__heading"
							data-badge-content={ themeIssues.length }
						>
							{ __( 'Themes with AMP incompatibilty', 'amp' ) }
							<VisuallyHidden as="span">
								{ `(${ themeIssues.length })` }
							</VisuallyHidden>
						</p>
					</div>
					<div className="site-scan__content">
						<ThemesWithIssues issues={ themeIssues } />
						<p className="site-scan__cta">
							<ExternalLink href={ VALIDATED_URLS_LINK }>
								{ __( 'AMP Validated URLs page', 'amp' ) }
							</ExternalLink>
						</p>
					</div>
				</Selectable>
			) }
			{ hasPluginIssues && (
				<Selectable className="site-scan__section site-scan__section--compact">
					<div className="site-scan__header">
						<IconLaptopPlug />
						<p
							className="site-scan__heading"
							data-badge-content={ pluginIssues.length }
						>
							{ __( 'Plugins with AMP incompatibility', 'amp' ) }
							<VisuallyHidden as="span">
								{ `(${ pluginIssues.length })` }
							</VisuallyHidden>
						</p>
					</div>
					<div className="site-scan__content">
						<PluginsWithIssues issues={ pluginIssues } />
						<p className="site-scan__cta">
							<ExternalLink href={ VALIDATED_URLS_LINK }>
								{ __( 'AMP Validated URLs page', 'amp' ) }
							</ExternalLink>
						</p>
					</div>
				</Selectable>
			) }
		</div>
	);
}

/**
 * Render a list of themes that cause issues.
 *
 * @param {Object} props        Component props.
 * @param {Array}  props.issues List of theme issues.
 */
function ThemesWithIssues( { issues = [] } ) {
	const themesData = useNormalizedThemesData();
	const themesWithIssues = issues?.map( ( slug ) => themesData?.[ slug ] ?? { name: slug } ) || [];

	if ( themesWithIssues.length === 0 ) {
		return <Loading />;
	}

	return <SourcesList sources={ themesWithIssues } />;
}

ThemesWithIssues.propTypes = {
	issues: PropTypes.array.isRequired,
};

/**
 * Render a list of plugins that cause issues.
 *
 * @param {Object} props        Component props.
 * @param {Array}  props.issues List of plugins issues.
 */
function PluginsWithIssues( { issues = [] } ) {
	const pluginsData = useNormalizedPluginsData();
	const pluginsWithIssues = issues?.map( ( slug ) => pluginsData?.[ slug ] ?? { name: slug } ) || [];

	if ( pluginsWithIssues.length === 0 ) {
		return <Loading />;
	}

	return <SourcesList sources={ pluginsWithIssues } />;
}

PluginsWithIssues.propTypes = {
	issues: PropTypes.array.isRequired,
};
