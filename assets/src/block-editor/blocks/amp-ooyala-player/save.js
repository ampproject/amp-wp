/**
 * External dependencies
 */
import PropTypes from 'prop-types';

const BlockSave = ( { attributes } ) => {
	const { dataEmbedCode, dataPlayerId, dataPcode, dataPlayerVersion, ampLayout, height, width } = attributes;

	const ooyalaProps = {
		layout: ampLayout,
		height,
		'data-embedcode': dataEmbedCode,
		'data-playerid': dataPlayerId,
		'data-pcode': dataPcode,
		'data-playerversion': dataPlayerVersion,
	};
	if ( 'fixed-height' !== ampLayout && width ) {
		ooyalaProps.width = width;
	}
	return (
		<amp-ooyala-player { ...ooyalaProps }></amp-ooyala-player>
	);
};

BlockSave.propTypes = {
	attributes: PropTypes.shape( {
		ampLayout: PropTypes.string,
		height: PropTypes.number,
		width: PropTypes.number,
		dataEmbedCode: PropTypes.string,
		dataPlayerId: PropTypes.string,
		dataPcode: PropTypes.string,
		dataPlayerVersion: PropTypes.string,
	} ),
};

export default BlockSave;
