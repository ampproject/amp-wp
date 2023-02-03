/**
 * WordPress dependencies
 */
import { useContext, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { SiteScan } from '../../components/site-scan-context-provider';
import {
	AMP_ADMIN_NOTICE_TYPE_ERROR,
	AMP_ADMIN_NOTICE_TYPE_INFO,
	AMP_ADMIN_NOTICE_TYPE_SUCCESS,
	AMP_ADMIN_NOTICE_TYPE_WARNING,
	AmpAdminNotice,
} from '../../components/amp-admin-notice';
import { Loading } from '../../components/loading';
import { PluginsWithAmpIncompatibility } from './plugins-with-amp-incompatibility';
import { ThemesWithAmpIncompatibility } from './themes-with-amp-incompatibility';

export function SiteScanNotice() {
	const {
		cancelSiteScan,
		fetchScannableUrls,
		isCancelled,
		isCompleted,
		isFailed,
		isInitializing,
		isReady,
		pluginsWithAmpIncompatibility,
		themesWithAmpIncompatibility,
		startSiteScan,
	} = useContext(SiteScan);

	// Cancel scan on component unmount.
	useEffect(() => cancelSiteScan, [cancelSiteScan]);

	// Fetch scannable URLs on mount. Start site scan right after the component is mounted and the scanner is ready.
	useEffect(() => {
		if (isInitializing) {
			fetchScannableUrls();
		} else if (isReady) {
			startSiteScan();
		}
	}, [fetchScannableUrls, isInitializing, isReady, startSiteScan]);

	const commonNoticeProps = {
		className: 'amp-site-scan-notice',
		isDismissible: true,
		onDismiss: cancelSiteScan,
	};

	if (isFailed || isCancelled) {
		return (
			<AmpAdminNotice
				type={AMP_ADMIN_NOTICE_TYPE_ERROR}
				{...commonNoticeProps}
			>
				<p>
					{__(
						'AMP could not check your site for compatibility issues.',
						'amp'
					)}
				</p>
			</AmpAdminNotice>
		);
	}

	if (isCompleted) {
		let elements = [
			pluginsWithAmpIncompatibility.length > 0 ? (
				<PluginsWithAmpIncompatibility
					key={pluginsWithAmpIncompatibility.length}
					pluginsWithAmpIncompatibility={
						pluginsWithAmpIncompatibility
					}
				/>
			) : null,
			themesWithAmpIncompatibility.length > 0 ? (
				<ThemesWithAmpIncompatibility
					key={themesWithAmpIncompatibility.length}
					themesWithAmpIncompatibility={themesWithAmpIncompatibility}
				/>
			) : null,
		];

		// Display the theme information at the top when on the `themes.php` screen.
		if (document.location.href.includes('themes.php')) {
			elements = elements.reverse();
		}

		elements = elements.filter(Boolean);

		return (
			<AmpAdminNotice
				type={
					elements.length
						? AMP_ADMIN_NOTICE_TYPE_WARNING
						: AMP_ADMIN_NOTICE_TYPE_SUCCESS
				}
				{...commonNoticeProps}
			>
				{elements.length ? (
					elements
				) : (
					<p>{__('No AMP compatibility issues detected.', 'amp')}</p>
				)}
			</AmpAdminNotice>
		);
	}

	return (
		<AmpAdminNotice
			type={AMP_ADMIN_NOTICE_TYPE_INFO}
			{...commonNoticeProps}
		>
			<p>
				{__('Checking your site for AMP compatibility issues…', 'amp')}
				<Loading inline={true} />
			</p>
		</AmpAdminNotice>
	);
}
