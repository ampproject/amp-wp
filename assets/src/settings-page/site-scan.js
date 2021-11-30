/**
 * External dependencies
 */
import { AMP_SCAN_IF_STALE, VALIDATED_URLS_LINK } from 'amp-settings'; // From WP inline script.
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { useCallback, useContext, useEffect, useMemo, useState } from '@wordpress/element';
import { getPathAndQueryString, hasQueryArg, removeQueryArgs } from '@wordpress/url';

/**
 * Internal dependencies
 */
import { AMPDrawer } from '../components/amp-drawer';
import { IconLandscapeHillsCogsAlt } from '../components/svg/landscape-hills-cogs-alt';
import { ProgressBar } from '../components/progress-bar';
import { PluginsWithAmpIncompatibility, ThemesWithAmpIncompatibility } from '../components/site-scan-results';
import { SiteScan as SiteScanContext } from '../components/site-scan-context-provider';
import { User } from '../components/user-context-provider';
import { Loading } from '../components/loading';
import {
	AMPNotice,
	NOTICE_SIZE_LARGE,
	NOTICE_SIZE_SMALL,
	NOTICE_TYPE_ERROR,
	NOTICE_TYPE_INFO,
	NOTICE_TYPE_PLAIN,
	NOTICE_TYPE_SUCCESS,
} from '../components/amp-notice';
import useDelayedFlag from '../utils/use-delayed-flag';

/**
 * Site Scan component on the settings screen.
 *
 * @param {Object}   props            Component props.
 * @param {Function} props.onSiteScan On scan callback.
 */
export function SiteScan( { onSiteScan } ) {
	const {
		cancelSiteScan,
		fetchScannableUrls,
		hasSiteScanResults,
		isBusy,
		isCancelled,
		isCompleted,
		isFailed,
		isFetchingScannableUrls,
		isReady,
		isSiteScannable,
		pluginsWithAmpIncompatibility,
		previewPermalink,
		stale,
		startSiteScan,
		themesWithAmpIncompatibility,
	} = useContext( SiteScanContext );
	const hasSiteIssues = themesWithAmpIncompatibility.length > 0 || pluginsWithAmpIncompatibility.length > 0;

	/**
	 * Fetch scannable URLs on mount; cancel site scan if the component unmounts.
	 */
	useEffect( () => {
		fetchScannableUrls();

		return cancelSiteScan;
	}, [ cancelSiteScan, fetchScannableUrls ] );

	/**
	 * If the results are stale and the user is coming from the Onboarding
	 * Wizard, a site scan should be triggered right away.
	 */
	useEffect( () => {
		const path = getPathAndQueryString( document.location.href );

		if ( ! isReady || ! hasQueryArg( path, AMP_SCAN_IF_STALE ) ) {
			return;
		}

		if ( stale ) {
			startSiteScan();
		}

		window.history.replaceState( {}, '', removeQueryArgs( path, AMP_SCAN_IF_STALE ) );
	}, [ isReady, stale, startSiteScan ] );

	/**
	 * Delay the `isCompleted` flag so that the progress bar stays at 100% for a
	 * brief moment.
	 */
	const isDelayedCompleted = useDelayedFlag( isCompleted );
	const isSummary = isReady || isDelayedCompleted;

	/**
	 * Check if the scanner has been triggered at least once in the current
	 * session by a user.
	 */
	const [ hasSiteScanBeenTriggered, setHasSiteScanBeenTriggered ] = useState( false );
	useEffect( () => {
		if ( ! hasSiteScanBeenTriggered && isBusy ) {
			setHasSiteScanBeenTriggered( true );
		}
	}, [ hasSiteScanBeenTriggered, isBusy ] );

	/**
	 * Get main content.
	 */
	const getContent = useCallback( () => {
		if ( isFetchingScannableUrls ) {
			return <Loading />;
		}

		if ( isFailed ) {
			return (
				<AMPNotice type={ NOTICE_TYPE_ERROR } size={ NOTICE_SIZE_LARGE }>
					<p>
						{ __( 'Site scan failed. Try again.', 'amp' ) }
					</p>
				</AMPNotice>
			);
		}

		if ( isCancelled ) {
			return (
				<AMPNotice type={ NOTICE_TYPE_ERROR } size={ NOTICE_SIZE_LARGE }>
					<p>
						{ __( 'Site scan has been cancelled. Try again.', 'amp' ) }
					</p>
				</AMPNotice>
			);
		}

		if ( ! isSiteScannable ) {
			return (
				<AMPNotice type={ NOTICE_TYPE_ERROR } size={ NOTICE_SIZE_LARGE }>
					<p>
						{ __( 'Your site cannot be scanned. There are no AMP-enabled URLs available.', 'amp' ) }
					</p>
				</AMPNotice>
			);
		}

		if ( isSummary ) {
			return <SiteScanSummary />;
		}

		return <SiteScanInProgress />;
	}, [ isCancelled, isFailed, isFetchingScannableUrls, isSiteScannable, isSummary ] );

	return (
		<SiteScanDrawer
			initialOpen={ ! hasSiteScanResults || stale || ! ( isReady && ! hasSiteIssues && ! hasSiteScanBeenTriggered ) }
			labelExtra={ stale && ( isReady || isDelayedCompleted ) ? (
				<AMPNotice type={ NOTICE_TYPE_PLAIN } size={ NOTICE_SIZE_SMALL }>
					{ __( 'Stale results', 'amp' ) }
				</AMPNotice>
			) : null }
			footerContent={ isSiteScannable && ( isSummary || isFailed || isCancelled ) && (
				<>
					<Button
						onClick={ () => {
							if ( typeof onSiteScan === 'function' ) {
								onSiteScan();
							}
							startSiteScan();
						} }
						isPrimary={ true }
					>
						{ hasSiteScanResults ? __( 'Rescan Site', 'amp' ) : __( 'Scan Site', 'amp' ) }
					</Button>
					{ hasSiteScanResults && (
						<Button href={ previewPermalink } isLink={ true }>
							{ __( 'Browse Site', 'amp' ) }
						</Button>
					) }
				</>
			) }
		>
			{ getContent() }
		</SiteScanDrawer>
	);
}
SiteScan.propTypes = {
	onSiteScan: PropTypes.func,
};

/**
 * Site Scan drawer (settings panel).
 *
 * @param {Object} props               Component props.
 * @param {any}    props.children      Component children.
 * @param {Object} props.footerContent Component footer content.
 */
function SiteScanDrawer( { children, footerContent, ...props } ) {
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
			{ ...props }
		>
			<div className="settings-site-scan">
				{ children }
				{ footerContent && (
					<div className="settings-site-scan__footer">
						{ footerContent }
					</div>
				) }
			</div>
		</AMPDrawer>
	);
}
SiteScanDrawer.propTypes = {
	children: PropTypes.any,
	footerContent: PropTypes.node,
};

/**
 * Site Scan - in progress state.
 */
function SiteScanInProgress() {
	const {
		isCompleted,
		scannableUrls,
		scannedUrlsMaxIndex,
	} = useContext( SiteScanContext );

	return (
		<>
			<p>
				{ __( 'Site scan is checking if there are AMP compatibility issues with your active theme and plugins. We’ll then recommend how to use the AMP plugin.', 'amp' ) }
			</p>
			<ProgressBar value={ isCompleted
				? 100
				: ( scannedUrlsMaxIndex / scannableUrls.length * 100 )
			} />
			<p className="settings-site-scan__status">
				{ isCompleted
					? __( 'Scan complete', 'amp' )
					: sprintf(
						// translators: 1: currently scanned URL index; 2: scannable URLs count; 3: scanned page type.
						__( 'Scanning %1$d/%2$d URLs: Checking %3$s…', 'amp' ),
						scannedUrlsMaxIndex + 1,
						scannableUrls.length,
						scannableUrls[ scannedUrlsMaxIndex ]?.label,
					)
				}
			</p>
		</>
	);
}

/**
 * Site Scan - summary state.
 */
function SiteScanSummary() {
	const {
		hasSiteScanResults,
		isReady,
		pluginsWithAmpIncompatibility,
		stale,
		themesWithAmpIncompatibility,
	} = useContext( SiteScanContext );
	const hasSiteIssues = themesWithAmpIncompatibility.length > 0 || pluginsWithAmpIncompatibility.length > 0;
	const { developerToolsOption } = useContext( User );
	const userIsTechnical = useMemo( () => developerToolsOption === true, [ developerToolsOption ] );

	if ( isReady && ! hasSiteScanResults ) {
		return (
			<AMPNotice type={ NOTICE_TYPE_INFO } size={ NOTICE_SIZE_LARGE }>
				<p>
					{ __( 'The site has not been scanned yet. Scan your site to ensure everything is working properly.', 'amp' ) }
				</p>
			</AMPNotice>
		);
	}

	if ( ! hasSiteIssues && ! stale ) {
		return (
			<AMPNotice type={ NOTICE_TYPE_SUCCESS } size={ NOTICE_SIZE_LARGE }>
				<p>
					{ __( 'Site scan found no issues on your site. Browse your site to ensure everything is working as expected.', 'amp' ) }
				</p>
			</AMPNotice>
		);
	}

	return (
		<>
			{ stale && (
				<AMPNotice type={ NOTICE_TYPE_INFO } size={ NOTICE_SIZE_LARGE }>
					<p>
						{ __( 'Stale results. Rescan your site to ensure everything is working properly.', 'amp' ) }
					</p>
				</AMPNotice>
			) }
			{ ! stale && isReady && (
				<AMPNotice type={ NOTICE_TYPE_INFO } size={ NOTICE_SIZE_LARGE }>
					<p>
						{ __( 'No changes since your last scan.', 'amp' ) }
					</p>
				</AMPNotice>
			) }
			{ themesWithAmpIncompatibility.length > 0 && (
				<ThemesWithAmpIncompatibility
					slugs={ themesWithAmpIncompatibility }
					showHelpText={ true }
					callToAction={ userIsTechnical && ! stale ? (
						<a href={ VALIDATED_URLS_LINK }>
							{ __( 'Review Validated URLs', 'amp' ) }
						</a>
					) : null }
				/>
			) }
			{ pluginsWithAmpIncompatibility.length > 0 && (
				<PluginsWithAmpIncompatibility
					slugs={ pluginsWithAmpIncompatibility }
					showHelpText={ true }
					callToAction={ userIsTechnical && ! stale ? (
						<a href={ VALIDATED_URLS_LINK }>
							{ __( 'Review Validated URLs', 'amp' ) }
						</a>
					) : null }
				/>
			) }
		</>
	);
}
