/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { PlainText } from '@wordpress/block-editor';

const BlockEdit = ( { attributes, setAttributes } ) => {
	const { dataFormula } = attributes;

	return (
		<PlainText
			value={ dataFormula }
			placeholder={ __( 'Insert formula', 'amp' ) }
			onChange={ ( value ) => setAttributes( { dataFormula: value } ) }
		/>
	);
};

BlockEdit.propTypes = {
	attributes: PropTypes.shape( {
		dataFormula: PropTypes.string,
	} ),
	setAttributes: PropTypes.func.isRequired,
};

export default BlockEdit;
