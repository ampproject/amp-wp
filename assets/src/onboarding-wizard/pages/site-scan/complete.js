/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Selectable } from '../../../components/selectable';
import { IconLandscapeHillsCogs } from '../../../components/svg/landscape-hills-cogs';

/**
 * Screen with site scan summary.
 */
export function SiteScanComplete() {
	return (
		<div className="site-scan">
			<Selectable>
				<div className="site-scan__header">
					<IconLandscapeHillsCogs />
					<p className="site-scan__heading">
						{ __( 'Scan complete', 'amp' ) }
					</p>
				</div>
				<p>
					{ __( 'Site scan found issues on your site. Proceed to the next step to follow recommendations for choosing a template mode.', 'amp' ) }
				</p>
			</Selectable>
		</div>
	);
}
