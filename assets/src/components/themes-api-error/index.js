/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { AMPNotice, NOTICE_TYPE_WARNING } from '../amp-notice';
import { ReaderThemes } from '../reader-themes-context-provider';

/**
 * Notice showing a message when the WordPress.org themes API request has failed on the backend.
 */
export function ThemesAPIError() {
	const { themesAPIError } = useContext( ReaderThemes );

	if ( ! themesAPIError ) {
		return null;
	}

	return (
		<AMPNotice type={ NOTICE_TYPE_WARNING }>
			<p>
				{
					__( 'There was an error fetching the default reader themes from WordPress.org. Please try again later. Contact your hosting provider if the issue persists.', 'amp' )
				}
			</p>
		</AMPNotice>
	);
}
