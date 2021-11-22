/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import {
	AMPNotice,
	NOTICE_SIZE_SMALL,
	NOTICE_TYPE_INFO,
	NOTICE_TYPE_SUCCESS,
	NOTICE_TYPE_WARNING,
} from '../amp-notice';

/**
 * Render notices on site support page.
 *
 * @param {Object} props                       Component props.
 * @param {Object} props.data                  Support data.
 * @param {Object} props.args                  Support argument.
 * @param {Object} props.ampValidatedPostCount AMP validated post counts.
 * @return {JSX.Element|null} HTML markup for notice data.
 */
export function ValidationResultsNotice( { data, args, ampValidatedPostCount } ) {
	const isSpecificUrlRequest = 0 < args?.urls?.length;
	const hasErrors = 0 < data.errors?.length || 0 < data.urls?.length;
	const hasAllStaleResult = 0 < ampValidatedPostCount.all && ampValidatedPostCount.all === ampValidatedPostCount.stale;
	const hasSomeStaleResult = 0 < ampValidatedPostCount.fresh && 0 < ampValidatedPostCount.stale;
	const hasAllFreshResult = 0 < ampValidatedPostCount.all && ampValidatedPostCount.all === ampValidatedPostCount.fresh;

	// The site doesn't have any validated URLs.
	if ( ! isSpecificUrlRequest && 0 === ampValidatedPostCount.all ) {
		return (
			<AMPNotice type={ NOTICE_TYPE_WARNING } size={ NOTICE_SIZE_SMALL }>
				{ __( 'The site has no validation data. Go to the AMP Settings page and scan you site before sending a support request.', 'amp' ) }
			</AMPNotice>
		);
	}

	// All validated URLs of site is stale.
	if ( ! isSpecificUrlRequest && hasAllStaleResult ) {
		return (
			<AMPNotice type={ NOTICE_TYPE_WARNING } size={ NOTICE_SIZE_SMALL }>
				{ __( 'The validation data is stale. Go to the AMP Settings page and rescan you site before sending a support request.', 'amp' ) }
			</AMPNotice>
		);
	}

	// The site doesn't have any AMP errors but there are some stalled result.
	if ( ! isSpecificUrlRequest && ! hasErrors && hasSomeStaleResult ) {
		return (
			<AMPNotice type={ NOTICE_TYPE_WARNING } size={ NOTICE_SIZE_SMALL }>
				{ __( 'We found no issues on your site but there are some stale validation results. Browse your site to ensure everything is working as expected.', 'amp' ) }
			</AMPNotice>
		);
	}

	// All validated URLs of site is stale.
	if ( ! isSpecificUrlRequest && ! hasErrors && hasAllFreshResult ) {
		return (
			<AMPNotice type={ NOTICE_TYPE_INFO } size={ NOTICE_SIZE_SMALL }>
				{ __( 'We found no issues on your site. Browse your site to ensure everything is working as expected.', 'amp' ) }
			</AMPNotice>
		);
	}

	// If requested URL doesn't have AMP related errors.
	if ( isSpecificUrlRequest && ! hasErrors ) {
		return (
			<AMPNotice type={ NOTICE_TYPE_SUCCESS } size={ NOTICE_SIZE_SMALL }>
				{ __( 'The requested URL does not have any AMP validation errors.', 'amp' ) }
			</AMPNotice>
		);
	}

	return null;
}

ValidationResultsNotice.propTypes = {
	args: PropTypes.any,
	data: PropTypes.shape( {
		error_sources: PropTypes.array.isRequired,
		errors: PropTypes.array.isRequired,
		plugins: PropTypes.array,
		site_info: PropTypes.object,
		themes: PropTypes.array,
		urls: PropTypes.array,
	} ),
	ampValidatedPostCount: PropTypes.shape( {
		all: PropTypes.number.isRequired,
		fresh: PropTypes.number.isRequired,
		stale: PropTypes.number.isRequired,
	} ),
};
