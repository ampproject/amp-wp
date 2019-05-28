/**
 * External dependencies
 */
import PropTypes from 'prop-types';

const BlockSave = ( { attributes } ) => {
	const { dataSiteId, dataPlayerId, dataContentId, dataDomain, dataMode, dataItems, ampLayout, height, width } = attributes;
	const springboardProps = {
		layout: ampLayout,
		height,
		'data-site-id': dataSiteId,
		'data-mode': dataMode,
		'data-content-id': dataContentId,
		'data-player-id': dataPlayerId,
		'data-domain': dataDomain,
		'data-items': dataItems,
	};
	if ( 'fixed-height' !== ampLayout && width ) {
		springboardProps.width = attributes.width;
	}
	return (
		<amp-springboard-player { ...springboardProps }></amp-springboard-player>
	);
};

BlockSave.propTypes = {
	attributes: PropTypes.shape( {
		dataSiteId: PropTypes.string,
		dataPlayerId: PropTypes.string,
		dataContentId: PropTypes.string,
		dataDomain: PropTypes.string,
		dataMode: PropTypes.oneOf( [ 'video', 'playlist' ] ),
		dataItems: PropTypes.number,
		ampLayout: PropTypes.string,
		height: PropTypes.number,
		width: PropTypes.number,
	} ),
};

export default BlockSave;
