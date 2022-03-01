/**
 * External dependencies
 */
import { VALIDATED_URLS_LINK } from 'amp-settings'; // From WP inline script.
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useContext, useEffect, useMemo } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { ExternalLink } from '@wordpress/components';

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
import { PluginsWithAmpIncompatibility, ThemesWithAmpIncompatibility } from '../../../components/site-scan-results';
import useDelayedFlag from '../../../utils/use-delayed-flag';

/**
 * Screen for visualizing a site scan.
 */
export function SiteScan() {
	const { setCanGoForward } = useContext( Navigation );
	const {
		cancelSiteScan,
		isCancelled,
		isCompleted,
		isFailed,
		isFetchingScannableUrls,
		isReady,
		pluginsWithAmpIncompatibility,
		scannableUrls,
		scannedUrlsMaxIndex,
		startSiteScan,
		themesWithAmpIncompatibility,
	} = useContext( SiteScanContext );
	const { developerToolsOption } = useContext( User );
	const userIsTechnical = useMemo( () => developerToolsOption === true, [ developerToolsOption ] );

	/**
	 * Cancel scan on component unmount.
	 */
	useEffect( () => () => cancelSiteScan(), [ cancelSiteScan ] );

	useEffect( () => {
		if ( isReady || isCancelled ) {
			startSiteScan();
		}
	}, [ isCancelled, isReady, startSiteScan ] );

	/**
	 * Allow moving forward.
	 */
	useEffect( () => {
		if ( isCompleted || isFailed ) {
			setCanGoForward( true );
		}
	}, [ isCompleted, isFailed, setCanGoForward ] );

	/**
	 * Delay the `isCompleted` flag so that the progress bar stays at 100% for a
	 * brief moment.
	 */
	const isDelayedCompleted = useDelayedFlag( isCompleted );

	if ( isFetchingScannableUrls || isReady ) {
		return (
			<SiteScanPanel
				title={ __( 'Please wait a minute…', 'amp' ) }
				headerContent={ <Loading /> }
			/>
		);
	}

	if ( isFailed ) {
		return (
			<SiteScanPanel
				title={ __( 'Scan failed', 'amp' ) }
				headerContent={ (
					<>
						<p>
							{ __( 'Site scan was unsuccessful.', 'amp' ) }
						</p>
						<p>
							{ __( 'You can trigger the site scan again on the AMP Settings page after completing the Wizard.', 'amp' ) }
						</p>
					</>
				) }
			/>
		);
	}

	if ( isDelayedCompleted ) {
		return (
			<SiteScanPanel
				title={ __( 'Scan complete', 'amp' ) }
				headerContent={ (
					<p>
						{ themesWithAmpIncompatibility.length > 0 || pluginsWithAmpIncompatibility.length > 0
							? __( 'Site scan found issues on your site. Proceed to the next step to follow recommendations for choosing a template mode.', 'amp' )
							: __( 'Site scan found no issues on your site. Proceed to the next step to follow recommendations for choosing a template mode.', 'amp' )
						}
					</p>
				) }
			>
				{ themesWithAmpIncompatibility.length > 0 && (
					<ThemesWithAmpIncompatibility
						className="site-scan__section"
						slugs={ themesWithAmpIncompatibility.map( ( { slug } ) => slug ) }
						callToAction={ userIsTechnical ? (
							<ExternalLink href={ VALIDATED_URLS_LINK }>
								{ __( 'Review Validated URLs', 'amp' ) }
							</ExternalLink>
						) : null }
					/>
				) }
				{ pluginsWithAmpIncompatibility.length > 0 && (
					<PluginsWithAmpIncompatibility
						className="site-scan__section"
						slugs={ pluginsWithAmpIncompatibility.map( ( { slug } ) => slug ) }
						callToAction={ userIsTechnical ? (
							<ExternalLink href={ VALIDATED_URLS_LINK }>
								{ __( 'Review Validated URLs', 'amp' ) }
							</ExternalLink>
						) : null }
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
					<ProgressBar value={ isCompleted
						? 100
						: ( scannedUrlsMaxIndex / scannableUrls.length * 100 )
					} />
					<p className="site-scan__status">
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
