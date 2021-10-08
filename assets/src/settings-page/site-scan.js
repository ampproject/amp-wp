/**
 * External dependencies
 */
import { VALIDATED_URLS_LINK } from 'amp-settings'; // From WP inline script.
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { useContext, useEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { AMPDrawer } from '../components/amp-drawer';
import { IconLandscapeHillsCogsAlt } from '../components/svg/landscape-hills-cogs-alt';
import { ProgressBar } from '../components/progress-bar';
import { PluginsWithIssues, ThemesWithIssues } from '../components/site-scan-results';
import { SiteScan as SiteScanContext } from '../components/site-scan-context-provider';
import { Loading } from '../components/loading';

/**
 * Site Scan component on the settings screen.
 */
export function SiteScan() {
	const {
		isInitializing,
		isReady,
		isBusy,
		isComplete,
		cancelSiteScan,
		currentlyScannedUrlIndex,
		pluginIssues,
		scannableUrls,
		startSiteScan,
		themeIssues,
	} = useContext( SiteScanContext );

	/**
	 * Cancel scan on component unmount.
	 */
	useEffect( () => () => cancelSiteScan(), [ cancelSiteScan ] );

	/**
	 * Show scan summary with a delay so that the progress bar has a chance to
	 * complete.
	 */
	const [ showSummary, setShowSummary ] = useState( true );

	useEffect( () => {
		let timeout;

		if ( ( isReady || isComplete ) && ! showSummary ) {
			timeout = setTimeout( () => setShowSummary( true ), 500 );
		}

		return () => {
			if ( timeout ) {
				clearTimeout( timeout );
			}
		};
	}, [ isComplete, isReady, showSummary ] );

	useEffect( () => {
		if ( showSummary && isBusy ) {
			setShowSummary( false );
		}
	}, [ isBusy, showSummary ] );

	if ( isInitializing ) {
		return (
			<SiteScanDrawer>
				<Loading />
			</SiteScanDrawer>
		);
	}

	if ( showSummary ) {
		return (
			<SiteScanDrawer>
				<div className="settings-site-scan">
					{ themeIssues.length > 0 && (
						<ThemesWithIssues
							issues={ themeIssues }
							validatedUrlsLink={ VALIDATED_URLS_LINK }
						/>
					) }
					{ pluginIssues.length > 0 && (
						<PluginsWithIssues
							issues={ pluginIssues }
							validatedUrlsLink={ VALIDATED_URLS_LINK }
						/>
					) }
				</div>
				<div className="settings-site-scan__footer">
					<Button
						onClick={ startSiteScan }
						isPrimary={ true }
					>
						{ __( 'Rescan Site', 'amp' ) }
					</Button>
				</div>
			</SiteScanDrawer>
		);
	}

	return (
		<SiteScanDrawer>
			<div className="settings-site-scan">
				<p>
					{ __( 'Site scan is checking if there are AMP compatibility issues with your active theme and plugins. We’ll then recommend how to use the AMP plugin.', 'amp' ) }
				</p>
				<ProgressBar value={ isComplete
					? 100
					: ( currentlyScannedUrlIndex / scannableUrls.length * 100 )
				} />
				<p>
					{ isComplete
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
		</SiteScanDrawer>
	);
}

/**
 * Site Scan drawer (settings panel).
 *
 * @param {Object} props          Component props.
 * @param {any}    props.children Component children.
 */
function SiteScanDrawer( { children } ) {
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
			{ children }
		</AMPDrawer>
	);
}
SiteScanDrawer.propTypes = {
	children: PropTypes.any,
};
