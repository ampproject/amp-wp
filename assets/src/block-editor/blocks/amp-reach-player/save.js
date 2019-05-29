/**
 * External dependencies
 */
import PropTypes from 'prop-types';

const BlockSave = ( { attributes } ) => {
	const { dataEmbedId, ampLayout, height, width } = attributes;

	const reachProps = {
		layout: ampLayout,
		height,
		'data-embed-id': dataEmbedId,
	};
	if ( 'fixed-height' !== ampLayout && width ) {
		reachProps.width = width;
	}
	return (
		<amp-reach-player { ...reachProps }></amp-reach-player>
	);
};

BlockSave.propTypes = {
	attributes: PropTypes.shape( {
		dataEmbedId: PropTypes.string,
		ampLayout: PropTypes.string,
		height: PropTypes.number,
		width: PropTypes.number,
	} ),
};

export default BlockSave;
