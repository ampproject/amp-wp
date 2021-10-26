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
import { useCallback, useContext, useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { AMPDrawer } from '../components/amp-drawer';
import { IconLandscapeHillsCogsAlt } from '../components/svg/landscape-hills-cogs-alt';
import { ProgressBar } from '../components/progress-bar';
import { PluginsWithAmpIncompatibility, ThemesWithAmpIncompatibility } from '../components/site-scan-results';
import { SiteScan as SiteScanContext } from '../components/site-scan-context-provider';
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
		didSiteScan,
		isCancelled,
		isCompleted,
		isFailed,
		isInitializing,
		isReady,
		previewPermalink,
		stale,
		startSiteScan,
	} = useContext( SiteScanContext );

	/**
	 * Cancel scan when component unmounts.
	 */
	useEffect( () => () => cancelSiteScan(), [ cancelSiteScan ] );

	/**
	 * Delay the `isCompleted` flag so that the progress bar stays at 100% for a
	 * brief moment.
	 */
	const isDelayedCompleted = useDelayedFlag( isCompleted );

	/**
	 * Get main content.
	 */
	const getContent = useCallback( () => {
		if ( isInitializing ) {
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

		if ( isReady || isDelayedCompleted ) {
			return <SiteScanSummary />;
		}

		return <SiteScanInProgress />;
	}, [ isCancelled, isDelayedCompleted, isFailed, isInitializing, isReady ] );

	/**
	 * Get footer content.
	 */
	const getFooterContent = useCallback( () => {
		function triggerSiteScan() {
			if ( typeof onSiteScan === 'function' ) {
				onSiteScan();
			}
			startSiteScan( { cache: true } );
		}

		if ( isCancelled || isFailed || ( stale && ( isReady || isDelayedCompleted ) ) ) {
			return (
				<Button onClick={ triggerSiteScan } isPrimary={ true }>
					{ __( 'Rescan Site', 'amp' ) }
				</Button>
			);
		}

		if ( ! didSiteScan ) {
			return (
				<Button onClick={ triggerSiteScan } isPrimary={ true }>
					{ __( 'Scan Site', 'amp' ) }
				</Button>
			);
		}

		if ( ! stale && isDelayedCompleted ) {
			return (
				<Button href={ previewPermalink } isPrimary={ true }>
					{ __( 'Browse Site', 'amp' ) }
				</Button>
			);
		}

		return null;
	}, [ didSiteScan, isCancelled, isDelayedCompleted, isFailed, isReady, onSiteScan, previewPermalink, stale, startSiteScan ] );

	return (
		<SiteScanDrawer
			initialOpen={ ! isReady || stale || ! didSiteScan }
			labelExtra={ stale && ( isReady || isDelayedCompleted ) ? (
				<AMPNotice type={ NOTICE_TYPE_PLAIN } size={ NOTICE_SIZE_SMALL }>
					{ __( 'Stale results', 'amp' ) }
				</AMPNotice>
			) : null }
			footerContent={ getFooterContent() }
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
		currentlyScannedUrlIndex,
		isCompleted,
		scannableUrls,
	} = useContext( SiteScanContext );

	return (
		<>
			<p>
				{ __( 'Site scan is checking if there are AMP compatibility issues with your active theme and plugins. We’ll then recommend how to use the AMP plugin.', 'amp' ) }
			</p>
			<ProgressBar value={ isCompleted
				? 100
				: ( currentlyScannedUrlIndex / scannableUrls.length * 100 )
			} />
			<p className="settings-site-scan__status">
				{ isCompleted
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
	);
}

/**
 * Site Scan - summary state.
 */
function SiteScanSummary() {
	const {
		didSiteScan,
		isReady,
		pluginsWithAMPIncompatibility,
		stale,
		themesWithAMPIncompatibility,
	} = useContext( SiteScanContext );
	const hasSiteIssues = themesWithAMPIncompatibility.length > 0 || pluginsWithAMPIncompatibility.length > 0;

	if ( isReady && ! didSiteScan ) {
		return (
			<AMPNotice
				type={ NOTICE_TYPE_INFO }
				size={ NOTICE_SIZE_LARGE }
			>
				<p>
					{ __( 'The site has not been scanned yet. Scan your site to ensure everything is working properly.', 'amp' ) }
				</p>
			</AMPNotice>
		);
	}

	return (
		<>
			{ isReady ? (
				<AMPNotice
					type={ stale ? NOTICE_TYPE_INFO : NOTICE_TYPE_SUCCESS }
					size={ NOTICE_SIZE_LARGE }
				>
					<p>
						{ stale
							? __( 'Stale results. Rescan your site to ensure everything is working properly.', 'amp' )
							: __( 'No changes since your last scan. Browse your site to ensure everything is working as expected.', 'amp' )
						}
					</p>
				</AMPNotice>
			) : (
				<>
					{ stale && (
						<AMPNotice type={ NOTICE_TYPE_INFO } size={ NOTICE_SIZE_LARGE }>
							<p>
								{ __( 'Stale results. Rescan your site to ensure everything is working properly.', 'amp' ) }
							</p>
						</AMPNotice>
					) }
					{ hasSiteIssues && (
						<p
							dangerouslySetInnerHTML={ {
								__html: sprintf(
									// translators: placeholders stand for page anchors.
									__( 'Because of issues we’ve uncovered, you’ll want to switch your template mode. Please see <a href="%1$s">template mode recommendations</a> below. Because of plugin issues, you may also want to <a href="%2$s">review and suppress plugins</a>.', 'amp' ),
									'#template-modes',
									'#plugin-suppression',
								),
							} }
						/>
					) }
					{ ! hasSiteIssues && ! stale && (
						<AMPNotice type={ NOTICE_TYPE_SUCCESS } size={ NOTICE_SIZE_LARGE }>
							<p>
								{ __( 'Site scan found no issues on your site.', 'amp' ) }
							</p>
						</AMPNotice>
					) }
				</>
			) }
			{ themesWithAMPIncompatibility.length > 0 && (
				<ThemesWithAmpIncompatibility
					slugs={ themesWithAMPIncompatibility }
					validatedUrlsLink={ stale ? '' : VALIDATED_URLS_LINK }
				/>
			) }
			{ pluginsWithAMPIncompatibility.length > 0 && (
				<PluginsWithAmpIncompatibility
					slugs={ pluginsWithAMPIncompatibility }
					validatedUrlsLink={ stale ? '' : VALIDATED_URLS_LINK }
				/>
			) }
		</>
	);
}
