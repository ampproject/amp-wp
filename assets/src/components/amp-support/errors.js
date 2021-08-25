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
 * To render error information on site support page.
 *
 * @param {Object} props        Component props.
 * @param {Array}  props.errors Error data.
 * @return {JSX.Element|null} HTML markup for error data.
 */
export function Errors( { errors } ) {
	if ( ! Array.isArray( errors ) ) {
		return null;
	}

	return (
		<Details
			title={ sprintf(
				/* translators: Placeholder is the number of errors */
				__( 'Errors (%d)', 'amp' ),
				errors.length,
			) }
			description={ __( 'Please check "Raw Data" for all error information.', 'amp' ) }
		/>
	);
}

Errors.propTypes = {
	errors: PropTypes.array.isRequired,
};

