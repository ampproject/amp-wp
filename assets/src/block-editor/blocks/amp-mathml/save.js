/**
 * External dependencies
 */
import PropTypes from 'prop-types';

const BlockSave = ( { attributes } ) => {
	const { dataFormula } = attributes;

	const mathmlProps = {
		'data-formula': dataFormula,
		layout: 'container',
	};
	return (
		<amp-mathml { ...mathmlProps }></amp-mathml>
	);
};

BlockSave.propTypes = {
	attributes: PropTypes.shape( {
		dataFormula: PropTypes.string,
	} ),
};

export default BlockSave;
