/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { SiteScan as SiteScanContext } from '../../../components/site-scan-context-provider';
import { Selectable } from '../../../components/selectable';
import { IconLandscapeHillsCogs } from '../../../components/svg/landscape-hills-cogs';
import { ProgressBar } from '../../../components/progress-bar';

/**
 * Screen for visualizing a site scan progress state.
 */
export function SiteScanInProgress() {
	const {
		currentlyScannedUrlIndex,
		scannableUrls,
		siteScanComplete,
	} = useContext( SiteScanContext );

	return (
		<div className="site-scan">
			<Selectable>
				<div className="site-scan__header">
					<IconLandscapeHillsCogs />
					<p className="site-scan__heading">
						{ __( 'Please wait a minute…', 'amp' ) }
					</p>
				</div>
				<p>
					{ __( 'Site scan is checking if there are AMP compatibility issues with your active theme and plugins. We’ll then recommend how to use the AMP plugin.', 'amp' ) }
				</p>
				<ProgressBar value={ siteScanComplete
					? 100
					: ( currentlyScannedUrlIndex / scannableUrls.length * 100 )
				} />
				<p>
					{ siteScanComplete
						? __( 'Scan complete', 'amp' )
						: sprintf(
							// translators: 1: currently scanned URL index; 2: scannable URLs count; 3: scanned page type.
							__( 'Scanning %1$d/%2$d URLs: Checking %3$s…', 'amp' ),
							currentlyScannedUrlIndex + 1,
							scannableUrls.length,
							scannableUrls[ currentlyScannedUrlIndex ]?.label,
						)
					}
				</p>
			</Selectable>
		</div>
	);
}
