/**
 * External dependencies
 */
import { VALIDATED_URLS_LINK } from 'amp-settings'; // From WP inline script.

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useContext, useEffect, useState } from '@wordpress/element';
import { Button } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { AMPDrawer } from '../components/amp-drawer';
import { IconLandscapeHillsCogsAlt } from '../components/svg/landscape-hills-cogs-alt';
import { ProgressBar } from '../components/progress-bar';
import { PluginsWithIssues, ThemesWithIssues } from '../components/site-scan-results';
import { SiteScan as SiteScanContext } from '../components/site-scan-context-provider';

/**
 * Site Scan component on the settings screen.
 */
export function SiteScan() {
	const {
		cancelSiteScan,
		canScanSite,
		startSiteScan,
		siteScanComplete,
	} = useContext( SiteScanContext );
	const [ requestSiteRescan, setRequestSiteRescan ] = useState( false );
	const [ showScanSummary, setShowScanSummary ] = useState( false );

	/**
	 * Cancel scan on component unmount.
	 */
	useEffect( () => () => cancelSiteScan(), [ cancelSiteScan ] );

	/**
	 * Start site scan.
	 */
	useEffect( () => {
		if ( canScanSite && requestSiteRescan ) {
			startSiteScan();
		}
	}, [ canScanSite, requestSiteRescan, startSiteScan ] );

	/**
	 * Show scan summary with a delay so that the progress bar has a chance to
	 * complete.
	 */
	useEffect( () => {
		let delay;

		if ( siteScanComplete && ! showScanSummary ) {
			delay = setTimeout( () => setShowScanSummary( true ), 500 );
		}

		return () => {
			if ( delay ) {
				clearTimeout( delay );
			}
		};
	}, [ showScanSummary, siteScanComplete ] );

	return (
		<AMPDrawer
			heading={ (
				<>
					<IconLandscapeHillsCogsAlt />
					{ __( 'Site Scan', 'amp' ) }
				</>
			) }
			hiddenTitle={ __( 'Site Scan', 'amp' ) }
			id="site-scan"
			initialOpen={ true }
		>
			{ ( showScanSummary || ! requestSiteRescan )
				? <SiteScanSummary />
				: <SiteScanInProgress /> }
			{ ! requestSiteRescan && (
				<div className="settings-site-scan__footer">
					<Button
						onClick={ () => setRequestSiteRescan( true ) }
						isPrimary={ true }
					>
						{ __( 'Rescan Site', 'amp' ) }
					</Button>
				</div>
			) }
		</AMPDrawer>
	);
}

/**
 * Scan in progress screen.
 */
function SiteScanInProgress() {
	const {
		currentlyScannedUrlIndex,
		scannableUrls,
		siteScanComplete,
	} = useContext( SiteScanContext );

	return (
		<div className="settings-site-scan">
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
		</div>
	);
}

/**
 * Scan summary screen.
 */
function SiteScanSummary() {
	const { pluginIssues, themeIssues } = useContext( SiteScanContext );
	const hasThemeIssues = themeIssues.length > 0;
	const hasPluginIssues = pluginIssues.length > 0;

	return (
		<div className="settings-site-scan">
			{ hasThemeIssues && (
				<ThemesWithIssues
					issues={ themeIssues }
					validatedUrlsLink={ VALIDATED_URLS_LINK }
				/>
			) }
			{ hasPluginIssues && (
				<PluginsWithIssues
					issues={ pluginIssues }
					validatedUrlsLink={ VALIDATED_URLS_LINK }
				/>
			) }
		</div>
	);
}
