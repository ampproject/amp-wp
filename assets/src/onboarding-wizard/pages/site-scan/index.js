/**
 * External dependencies
 */
import { VALIDATED_URLS_LINK } from 'amp-settings'; // From WP inline script.
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useContext, useEffect, useMemo, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './style.scss';
import { Navigation } from '../../components/navigation-context-provider';
import { SiteScan as SiteScanContext } from '../../../components/site-scan-context-provider';
import { User } from '../../../components/user-context-provider';
import { Loading } from '../../../components/loading';
import { Selectable } from '../../../components/selectable';
import { IconLandscapeHillsCogs } from '../../../components/svg/landscape-hills-cogs';
import { ProgressBar } from '../../../components/progress-bar';
import { PluginsWithIssues, ThemesWithIssues } from '../../../components/site-scan-results';

/**
 * Screen for visualizing a site scan.
 */
export function SiteScan() {
	const { setCanGoForward } = useContext( Navigation );
	const {
		isInitializing,
		isReady,
		isComplete,
		cancelSiteScan,
		currentlyScannedUrlIndex,
		pluginIssues,
		scannableUrls,
		startSiteScan,
		themeIssues,
	} = useContext( SiteScanContext );
	const { developerToolsOption } = useContext( User );
	const userIsTechnical = useMemo( () => developerToolsOption === true, [ developerToolsOption ] );
	/**
	 * Cancel scan on component unmount.
	 */
	useEffect( () => () => cancelSiteScan(), [ cancelSiteScan ] );

	useEffect( () => {
		if ( isReady ) {
			startSiteScan();
		}
	}, [ isReady, startSiteScan ] );

	/**
	 * Allow moving forward.
	 */
	useEffect( () => {
		if ( isComplete ) {
			setCanGoForward( true );
		}
	}, [ isComplete, setCanGoForward ] );

	/**
	 * Show scan summary with a delay so that the progress bar has a chance to
	 * complete.
	 */
	const [ showSummary, setShowSummary ] = useState( isComplete );

	useEffect( () => {
		let timeout;

		if ( isComplete && ! showSummary ) {
			timeout = setTimeout( () => setShowSummary( true ), 500 );
		}

		return () => {
			if ( timeout ) {
				clearTimeout( timeout );
			}
		};
	}, [ showSummary, isComplete ] );

	if ( isInitializing ) {
		return (
			<SiteScanPanel
				title={ __( 'Please wait a minute…', 'amp' ) }
				headerContent={ <Loading /> }
			/>
		);
	}

	if ( showSummary ) {
		return (
			<SiteScanPanel
				title={ __( 'Scan complete', 'amp' ) }
				headerContent={ (
					<p>
						{ themeIssues.length > 0 || pluginIssues.length > 0
							? __( 'Site scan found issues on your site. Proceed to the next step to follow recommendations for choosing a template mode.', 'amp' )
							: __( 'Site scan found no issues on your site. Proceed to the next step to follow recommendations for choosing a template mode.', 'amp' )
						}
					</p>
				) }
			>
				{ themeIssues.length > 0 && (
					<ThemesWithIssues
						className="site-scan__section"
						issues={ themeIssues }
						validatedUrlsLink={ userIsTechnical ? VALIDATED_URLS_LINK : null }
					/>
				) }
				{ pluginIssues.length > 0 && (
					<PluginsWithIssues
						className="site-scan__section"
						issues={ pluginIssues }
						validatedUrlsLink={ userIsTechnical ? VALIDATED_URLS_LINK : null }
					/>
				) }
			</SiteScanPanel>
		);
	}

	return (
		<SiteScanPanel
			title={ __( 'Please wait a minute…', 'amp' ) }
			headerContent={ (
				<>
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
				</>
			) }
		/>
	);
}

/**
 * Site Scan panel.
 *
 * @param {Object} props               Component props.
 * @param {any}    props.children      Component children.
 * @param {any}    props.headerContent Component header content.
 * @param {string} props.title         Component title.
 */
function SiteScanPanel( {
	children,
	headerContent,
	title,
} ) {
	return (
		<div className="site-scan">
			<Selectable className="site-scan__section">
				<div className="site-scan__header">
					<IconLandscapeHillsCogs />
					<p className="site-scan__heading">
						{ title }
					</p>
				</div>
				{ headerContent }
			</Selectable>
			{ children }
		</div>
	);
}
SiteScanPanel.propTypes = {
	children: PropTypes.any,
	headerContent: PropTypes.any,
	title: PropTypes.string,
};
