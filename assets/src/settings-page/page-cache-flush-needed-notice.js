/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import {
	SITE_HAS_CACHE_ENABLE,
} from 'amp-settings';

/**
 * Welcome component on the settings screen.
 */
export function PageCacheFlushNeededNotice() {
	if ( 'undefined' === typeof SITE_HAS_CACHE_ENABLE || true !== SITE_HAS_CACHE_ENABLE ) {
		return '';
	}

	const noticeID = 'amp-page-cache-flush-needed';

	/**
	 * Callback of click event of dismiss button.
	 */
	const dismissNotice = async () => {
		try {
			const noticeElement = document.getElementById( noticeID );
			const body = new global.FormData();
			body.append( 'action', 'dismiss-amp-notice' );
			body.append( 'notice', noticeID );

			const response = await global.fetch( global.ajaxurl, {
				body,
				method: 'POST',
			} );

			if ( 200 === response.status && null !== noticeElement ) {
				noticeElement.remove();
			}
		} catch ( exception ) {
		}
	};

	return (
		<div id={ noticeID } className="amp-plugin-notice notice notice-error is-dismissible">
			<div
				className="notice-dismiss"
				role="button"
				onClick={ dismissNotice }
				onKeyDown={ dismissNotice }
				tabIndex={ 0 }
			>
				&nbsp;
			</div>
			<div>
				<p>
					{ __( 'Please flush page cache.', 'amp' ) }
				</p>
			</div>
		</div>
	);
}
