/**
 * External dependencies
 */
import { HOME_URL, VALIDATED_URLS_LINK } from 'amp-settings'; // From WP inline script.
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
import { STANDARD } from '../common/constants';
import { AMPDrawer } from '../components/amp-drawer';
import { IconLandscapeHillsCogsAlt } from '../components/svg/landscape-hills-cogs-alt';
import { ProgressBar } from '../components/progress-bar';
import { PluginsWithIssues, ThemesWithIssues } from '../components/site-scan-results';
import { Options } from '../components/options-context-provider';
import { SiteScan as SiteScanContext } from '../components/site-scan-context-provider';
import { Loading } from '../components/loading';
import {
	AMPNotice,
	NOTICE_SIZE_LARGE,
	NOTICE_SIZE_SMALL,
	NOTICE_TYPE_INFO,
	NOTICE_TYPE_PLAIN,
	NOTICE_TYPE_SUCCESS,
} from '../components/amp-notice';

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
		stale,
		startSiteScan,
		themeIssues,
	} = useContext( SiteScanContext );
	const { originalOptions } = useContext( Options );
	const {
		paired_url_examples: pairedUrlExamples,
		paired_url_structure: pairedUrlStructure,
		theme_support: themeSupport,
	} = originalOptions;

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
			<SiteScanDrawer initialOpen={ true }>
				<Loading />
			</SiteScanDrawer>
		);
	}

	const hasSiteIssues = themeIssues.length > 0 || pluginIssues.length > 0;
	const previewPermalink = STANDARD === themeSupport ? HOME_URL : pairedUrlExamples[ pairedUrlStructure ][ 0 ];

	if ( showSummary ) {
		return (
			<SiteScanDrawer
				initialOpen={ stale || isComplete }
				labelExtra={ stale ? (
					<AMPNotice type={ NOTICE_TYPE_PLAIN } size={ NOTICE_SIZE_SMALL }>
						{ __( 'Stale results', 'amp' ) }
					</AMPNotice>
				) : null }
			>
				<div className="settings-site-scan">
					{ ! isComplete && (
						<AMPNotice type={ NOTICE_TYPE_INFO } size={ NOTICE_SIZE_LARGE }>
							<p>
								{ stale
									? __( 'Stale results. Rescan your site to ensure everything is working properly.', 'amp' )
									: __( 'No changes since your last scan. Browse your site to ensure everything is working as expected.', 'amp' )
								}
							</p>
						</AMPNotice>
					) }
					{ isComplete && hasSiteIssues && (
						<p
							dangerouslySetInnerHTML={ {
								__html: sprintf(
									// translators: placeholders stand for internal links.
									__( 'Because of issues we’ve uncovered, you’ll want to switch your template mode. Please see <a href="%1$s">template mode recommendations</a> below. Because of plugin issues, you may also want to <a href="%2$s">review and suppress plugins</a>.', 'amp' ),
									'#template-modes',
									'#plugin-suppression',
								),
							} }
						/>
					) }
					{ isComplete && ! hasSiteIssues && (
						<AMPNotice type={ NOTICE_TYPE_SUCCESS } size={ NOTICE_SIZE_LARGE }>
							<p>
								{ __( 'Site scan found no issues on your site.', 'amp' ) }
							</p>
						</AMPNotice>
					) }
					{ themeIssues.length > 0 && (
						<ThemesWithIssues
							issues={ themeIssues }
							validatedUrlsLink={ stale ? '' : VALIDATED_URLS_LINK }
						/>
					) }
					{ pluginIssues.length > 0 && (
						<PluginsWithIssues
							issues={ pluginIssues }
							validatedUrlsLink={ stale ? '' : VALIDATED_URLS_LINK }
						/>
					) }
					<div className="settings-site-scan__footer">
						{ isComplete
							? (
								<Button href={ previewPermalink } isPrimary={ true }>
									{ __( 'Browse Site', 'amp' ) }
								</Button>
							)
							: (
								<Button
									onClick={ () => startSiteScan( { cache: true } ) }
									isPrimary={ true }
								>
									{ __( 'Rescan Site', 'amp' ) }
								</Button>
							) }
					</div>
				</div>
			</SiteScanDrawer>
		);
	}

	return (
		<SiteScanDrawer initialOpen={ true }>
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
function SiteScanDrawer( { children, ...props } ) {
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
			{ children }
		</AMPDrawer>
	);
}
SiteScanDrawer.propTypes = {
	children: PropTypes.any,
};
