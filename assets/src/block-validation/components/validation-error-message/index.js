/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { ReactElement } from 'react';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Get message for validation error.
 *
 * @param {Object}  props Component props.
 * @param {?string} props.title Title for error (with HTML) as provided by \AMP_Validation_Error_Taxonomy::get_error_title_from_code().
 * @param {?string} props.code Error code.
 * @param {?string|ReactElement} props.message Error message.
 *
 * @return {ReactElement} Validation error message.
 */
const ValidationErrorMessage = ( { title, message, code } ) => {
	if ( message ) {
		return message; // @todo It doesn't appear this is ever set?
	}

	if ( title ) {
		return <span dangerouslySetInnerHTML={ { __html: title } } />;
	}

	return (
		<>
			{ __( 'Error code: ', 'amp' ) }
			<code>
				{ code || __( 'unknown', 'amp' ) }
			</code>
		</>
	);
};

ValidationErrorMessage.propTypes = {
	message: PropTypes.string,
	title: PropTypes.string,
	code: PropTypes.string,
};

export default ValidationErrorMessage;
