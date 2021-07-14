/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { RECHECK_URL } from 'amp-settings'; // From WP inline script.

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { RawHTML } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Loading } from '../components/loading';
import {
	AMPNotice,
	NOTICE_SIZE_LARGE,
	NOTICE_TYPE_INFO,
} from '../components/amp-notice';
import StylesheetsSummary from './components/stylesheets-summary';

/**
 * Stylesheets validation data.
 *
 * @param {Object} props Component props.
 * @param {boolean} props.fetching Flag indicating if stylesheets data is being fetched.
 * @param {Object} props.stats Stylesheets stats object.
 * @param {Object|Array} props.stylesheets Array of stylesheets details or an object containing errors.
 */
export default function Stylesheets( {
	fetching,
	stats,
	stylesheets,
} ) {
	if ( fetching !== false ) {
		return <Loading />;
	}

	if ( stylesheets?.errors?.amp_validated_url_stylesheets_no_longer_available ) {
		return (
			<AMPNotice size={ NOTICE_SIZE_LARGE } type={ NOTICE_TYPE_INFO }>
				<RawHTML>
					{ sprintf(
						/* translators: placeholder is URL to recheck the post */
						__( 'Stylesheet information for this URL is no longer available. Such data is automatically deleted after a week to reduce database storage. It is of little value to store long-term given that it becomes stale as themes and plugins are updated. To obtain the latest stylesheet information, <a href="%s">recheck this URL</a>.', 'amp' ),
						`${ RECHECK_URL }#amp_stylesheets`,
					) }
				</RawHTML>
			</AMPNotice>
		);
	}

	if ( stylesheets?.errors?.amp_validated_url_stylesheets_missing || stylesheets?.length === 0 || ! stats ) {
		return (
			<AMPNotice size={ NOTICE_SIZE_LARGE } type={ NOTICE_TYPE_INFO }>
				{ __( 'Unable to retrieve stylesheets data for this URL.', 'amp' ) }
			</AMPNotice>
		);
	}

	return <StylesheetsSummary stats={ stats } />;
}
Stylesheets.propTypes = {
	fetching: PropTypes.bool,
	stats: PropTypes.object,
	stylesheets: PropTypes.oneOf( [
		PropTypes.array,
		PropTypes.shape( {
			errors: PropTypes.object,
		} ),
	] ),
};
