/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useContext, useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import './style.scss';
import { Navigation } from '../../components/navigation-context-provider';
import { Selectable } from '../../../components/selectable';
import { IconLandscapeHillsCogs } from '../../../components/svg/landscape-hills-cogs';

/**
 * Screen for visualizing a site scan.
 */
export function SiteScan() {
	const { setCanGoForward } = useContext( Navigation );

	/**
	 * Allow moving forward.
	 */
	useEffect( () => {
		// @todo: Allow moving forward once the scan is complete.
		if ( true ) {
			setCanGoForward( true );
		}
	}, [ setCanGoForward ] );

	return (
		<div className="site-scan">
			<Selectable>
				<div className="site-scan__header">
					<IconLandscapeHillsCogs />
					<p className="site-scan__heading">
						{ __( 'Please wait a minute ...', 'amp' ) }
					</p>
				</div>
				<p>
					{ __( 'Site scan is checking if there are AMP compatibility issues with your active theme and plugins. We’ll then recommend how to use the AMP plugin.', 'amp' ) }
				</p>
			</Selectable>
		</div>
	);
}
