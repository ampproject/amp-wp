/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useContext } from '@wordpress/element';
import { VisuallyHidden } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { Selectable } from '../../../components/selectable';
import { SiteScan } from '../../../components/site-scan-context-provider';
import { IconLandscapeHillsCogs } from '../../../components/svg/landscape-hills-cogs';
import { IconWebsitePaintBrush } from '../../../components/svg/website-paint-brush';
import { IconLaptopPlug } from '../../../components/svg/laptop-plug';
import { useNormalizedPluginsData } from '../../../components/plugins-context-provider/use-normalized-plugins-data';
import { Loading } from '../../../components/loading';
import { SourcesList } from './sources-list';

/**
 * Screen with site scan summary.
 */
export function SiteScanComplete() {
	const {
		pluginIssues,
		themeIssues,
	} = useContext( SiteScan );

	const pluginsData = useNormalizedPluginsData();
	const pluginsWithIssues = pluginIssues.map( ( slug ) => pluginsData?.[ slug ] ?? { name: slug } );

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
					{ __( 'Site scan found issues on your site. Proceed to the next step to follow recommendations for choosing a template mode.', 'amp' ) }
				</p>
			</Selectable>
			{ themeIssues.length > 0 && (
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
					<div className="site-scan__content" />
				</Selectable>
			) }
			{ pluginIssues.length > 0 && (
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
						{ pluginsWithIssues.length === 0
							? <Loading />
							: <SourcesList sources={ pluginsWithIssues } /> }
					</div>
				</Selectable>
			) }
		</div>
	);
}
