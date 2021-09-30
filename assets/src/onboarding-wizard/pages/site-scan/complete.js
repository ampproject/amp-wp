/**
 * External dependencies
 */
import { VALIDATED_URLS_LINK } from 'amp-settings'; // From WP inline script.

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useContext, useMemo } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Selectable } from '../../../components/selectable';
import { SiteScan } from '../../../components/site-scan-context-provider';
import { User } from '../../../components/user-context-provider';
import { IconLandscapeHillsCogs } from '../../../components/svg/landscape-hills-cogs';
import {
	PluginsWithIssues,
	ThemesWithIssues,
} from '../../../components/site-scan-results';

/**
 * Screen with site scan summary.
 */
export function SiteScanComplete() {
	const { pluginIssues, themeIssues } = useContext( SiteScan );
	const hasThemeIssues = themeIssues.length > 0;
	const hasPluginIssues = pluginIssues.length > 0;

	const { developerToolsOption } = useContext( User );
	const userIsTechnical = useMemo( () => developerToolsOption === true, [ developerToolsOption ] );

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
				<ThemesWithIssues
					issues={ themeIssues }
					validatedUrlsLink={ userIsTechnical ? VALIDATED_URLS_LINK : null }
				/>
			) }
			{ hasPluginIssues && (
				<PluginsWithIssues
					issues={ pluginIssues }
					validatedUrlsLink={ userIsTechnical ? VALIDATED_URLS_LINK : null }
				/>
			) }
		</div>
	);
}
