/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Details } from './details';

/**
 * To render error source information on site support page.
 *
 * @param {Object} props      Component props.
 * @param {Object} props.data Error data.
 * @return {JSX.Element|null} HTML markup for error source data.
 */
export function ErrorSources( { data: errorSources } ) {
	if ( ! Array.isArray( errorSources ) ) {
		return null;
	}

	return (
		<Details
			title={ sprintf(
				/* translators: Placeholder is the number of error sources */
				__( 'Error Sources (%d)', 'amp' ),
				errorSources.length,
			) }
			description={ __( 'Please check "Raw Data" for all error source information.', 'amp' ) }
		/>
	);
}

ErrorSources.propTypes = {
	data: PropTypes.array.isRequired,
};

