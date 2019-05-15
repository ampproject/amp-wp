/**
 * WordPress dependencies
 */
import { Fragment } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Get message for validation error.
 *
 * @param {Object} error             Validation error.
 * @param {string} error.code        Error code.
 * @param {string} error.node_name   Node name.
 * @param {string} error.parent_name Parent node name.
 * @param {string} error.message     Error message.
 *
 * @return {Component} Validation error message.
 */
export default ( { message, code, node_name: nodeName, parent_name: parentName } ) => {
	if ( message ) {
		return message;
	}

	if ( 'invalid_element' === code && nodeName ) {
		return (
			<Fragment>
				{ __( 'Invalid element: ', 'amp' ) }
				<code>{ nodeName }</code>
			</Fragment>
		);
	} else if ( 'invalid_attribute' === code && nodeName ) {
		return (
			<Fragment>
				{ __( 'Invalid attribute: ', 'amp' ) }
				<code>{ parentName ? sprintf( '%s[%s]', parentName, nodeName ) : nodeName }</code>
			</Fragment>
		);
	}

	return (
		<Fragment>
			{ __( 'Error code: ', 'amp' ) }
			<code>{ code || __( 'unknown', 'amp' ) }</code>
		</Fragment>
	);
};
