/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * Get message for validation error.
 *
 * @param {?string} code       Error code.
 * @param {?string} nodeName   Node name.
 * @param {?string} parentName Parent node name.
 * @param {?string} message    Error message.
 *
 * @return {Component} Validation error message.
 */
const ValidationErrorMessage = ( { message, code, node_name: nodeName, parent_name: parentName } ) => {
	if ( message ) {
		return message;
	}

	if ( 'invalid_element' === code && nodeName ) {
		return (
			<>
				{ __( 'Invalid element: ', 'amp' ) }
				<code>{ nodeName }</code>
			</>
		);
	} else if ( 'invalid_attribute' === code && nodeName ) {
		return (
			<>
				{ __( 'Invalid attribute: ', 'amp' ) }
				<code>{ parentName ? sprintf( '%s[%s]', parentName, nodeName ) : nodeName }</code>
			</>
		);
	}

	return (
		<>
			{ __( 'Error code: ', 'amp' ) }
			<code>{ code || __( 'unknown', 'amp' ) }</code>
		</>
	);
};

ValidationErrorMessage.propTypes = {
	message: PropTypes.string,
	code: PropTypes.string,
	node_name: PropTypes.string,
	parent_name: PropTypes.string,
};

export default ValidationErrorMessage;
