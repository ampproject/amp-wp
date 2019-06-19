/**
 * External dependencies
 */
import PropTypes from 'prop-types';

const BlockSave = ( { attributes } ) => {
	const { width, height, ampLayout, dataPlaylistId, dataPlayerId, dataMediaId } = attributes;

	const jwProps = {
		layout: ampLayout,
		height,
		'data-player-id': dataPlayerId,
	};
	if ( 'fixed-height' !== ampLayout && width ) {
		jwProps.width = width;
	}
	if ( dataPlaylistId ) {
		jwProps[ 'data-playlist-id' ] = dataPlaylistId;
	}
	if ( dataMediaId ) {
		jwProps[ 'data-media-id' ] = dataMediaId;
	}
	return (
		<amp-jwplayer { ...jwProps }></amp-jwplayer>
	);
};

BlockSave.propTypes = {
	attributes: PropTypes.shape( {
		dataPlayerId: PropTypes.string,
		dataMediaId: PropTypes.string,
		dataPlaylistId: PropTypes.string,
		ampLayout: PropTypes.string,
		height: PropTypes.number,
		width: PropTypes.number,
	} ),
};

export default BlockSave;
