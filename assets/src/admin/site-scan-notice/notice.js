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
	ADMIN_NOTICE_TYPE_ERROR,
	ADMIN_NOTICE_TYPE_INFO,
	ADMIN_NOTICE_TYPE_SUCCESS,
	ADMIN_NOTICE_TYPE_WARNING,
	AdminNotice,
} from '../../components/admin-notice';
import { Loading } from '../../components/loading';

export function SiteScanNotice() {
	const {
		cancelSiteScan,
		isBusy,
		isCancelled,
		isCompleted,
		isFailed,
		isFetchingScannableUrls,
		isReady,
		pluginsWithAmpIncompatibility,
		startSiteScan,
	} = useContext( SiteScan );

	const hasIssues = pluginsWithAmpIncompatibility.length > 0;
	const failed = isFailed || isCancelled;
	const inProgress = isBusy || isFetchingScannableUrls || isReady;
	const foundNoIssues = isCompleted && ! hasIssues;
	const foundIssues = isCompleted && hasIssues;

	/**
	 * Start site scan right after component is mounted and scanner is ready. Cancel scan on unmount.
	 */
	useEffect( () => {
		if ( isReady ) {
			startSiteScan();
		}

		return cancelSiteScan;
	}, [ cancelSiteScan, isReady, startSiteScan ] );

	let noticeType = ADMIN_NOTICE_TYPE_INFO;
	if ( failed ) {
		noticeType = ADMIN_NOTICE_TYPE_ERROR;
	} else if ( foundNoIssues ) {
		noticeType = ADMIN_NOTICE_TYPE_SUCCESS;
	} else if ( foundIssues ) {
		noticeType = ADMIN_NOTICE_TYPE_WARNING;
	}

	return (
		<AdminNotice
			type={ noticeType }
			isDismissible={ true }
			onDismiss={ cancelSiteScan }
		>
			{ failed && <SiteScanFailed /> }
			{ inProgress && <SiteScanInProgress /> }
			{ foundNoIssues && <SiteScanFoundNoIssues /> }
			{ foundIssues && <SiteScanFoundIssues /> }
		</AdminNotice>
	);
}

function SiteScanInProgress() {
	return (
		<p>
			{ __( 'AMP plugin is checking you site for compatibility issues', 'amp' ) }
			<Loading inline={ true } />
		</p>
	);
}

function SiteScanFoundNoIssues() {
	return (
		<p>
			{ __( 'AMP plugin found no validation errors.', 'amp' ) }
		</p>
	);
}

function SiteScanFoundIssues() {
	return (
		<p>
			{ __( 'AMP Plugin found validation errors.', 'amp' ) }
		</p>
	);
}

function SiteScanFailed() {
	return (
		<p>
			{ __( 'AMP plugin could not check your site for compatibility issues.', 'amp' ) }
		</p>
	);
}
