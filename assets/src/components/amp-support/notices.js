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
 * @param {Object} props Component props.
 * @return {JSX.Element|null} HTML markup for notice data.
 */
export function Notices( props ) {
	const { data, args, ampValidatedPostCount } = props;

	const isSpecificUrlRequest = ( 0 < args?.urls?.length );
	const hasErrors = ( 0 < data?.errors?.length || 0 !== data?.urls?.length );
	const hasAllStalledResult = ( 0 !== ampValidatedPostCount.all && 0 === ampValidatedPostCount.valid );
	const hasSomeStalledResult = ( 0 !== ampValidatedPostCount.all && 0 !== ampValidatedPostCount.valid && ampValidatedPostCount.all !== ampValidatedPostCount.valid );
	const hasAllValidResult = ( 0 !== ampValidatedPostCount.all && ampValidatedPostCount.all === ampValidatedPostCount.valid );

	return (
		<>
			{ /* The site doesn't have any validated URLs. */ }
			{ ! isSpecificUrlRequest && 0 === ampValidatedPostCount.all && (
				<AMPNotice type={ NOTICE_TYPE_WARNING } size={ NOTICE_SIZE_SMALL }>
					{ __( 'The site has no validation data. Please scan your site from the "AMP Setting" page to send enough data for support requests.', 'amp' ) }
				</AMPNotice>
			) }

			{ /* All validated URLs of site is stalled. */ }
			{ ! isSpecificUrlRequest && hasAllStalledResult && (
				<AMPNotice type={ NOTICE_TYPE_WARNING } size={ NOTICE_SIZE_SMALL }>
					{ __( 'The validation data are stalled. Please re-scan your site from the "AMP Setting" page to send enough data for support requests.', 'amp' ) }
				</AMPNotice>
			) }

			{ /* The site doesn't have any AMP errors but there are some stalled result. */ }
			{ ! isSpecificUrlRequest && ! hasErrors && hasSomeStalledResult && (
				<AMPNotice type={ NOTICE_TYPE_WARNING } size={ NOTICE_SIZE_SMALL }>
					{ __( 'We found no issues on your site but there are some stalled result. Browse your site to ensure everything is working as expected.', 'amp' ) }
				</AMPNotice>
			) }

			{ /* All validated URLs of site is stalled. */ }
			{ ! isSpecificUrlRequest && ! hasErrors && hasAllValidResult && (
				<AMPNotice type={ NOTICE_TYPE_INFO } size={ NOTICE_SIZE_SMALL }>
					{ __( 'We found no issues on your site. Browse your site to ensure everything is working as expected.', 'amp' ) }
				</AMPNotice>
			) }

			{ /* If requested URL doesn't have AMP related errors. */ }
			{ isSpecificUrlRequest && ! hasErrors && (
				<AMPNotice type={ NOTICE_TYPE_SUCCESS } size={ NOTICE_SIZE_SMALL }>
					{ __( 'The requested URL does not have any AMP related errors.', 'amp' ) }
				</AMPNotice>
			) }
		</>
	);
}

Notices.propTypes = {
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
		valid: PropTypes.number.isRequired,
	} ),
};
