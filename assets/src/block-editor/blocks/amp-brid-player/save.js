/**
 * External dependencies
 */
import PropTypes from 'prop-types';

const BlockSave = ( { attributes } ) => {
	const { dataPlayer, dataOutstream, dataPartner, ampLayout, width, height, dataVideo, autoPlay, dataPlaylist } = attributes;

	const bridProps = {
		layout: ampLayout,
		height,
		'data-player': dataPlayer,
		'data-partner': dataPartner,
	};
	if ( 'fixed-height' !== ampLayout && width ) {
		bridProps.width = width;
	}
	if ( dataPlaylist ) {
		bridProps[ 'data-playlist' ] = dataPlaylist;
	}
	if ( dataVideo ) {
		bridProps[ 'data-video' ] = dataVideo;
	}
	if ( dataOutstream ) {
		bridProps[ 'data-outstream' ] = dataOutstream;
	}
	if ( autoPlay ) {
		bridProps.autoplay = autoPlay;
	}
	return (
		<amp-brid-player { ...bridProps }></amp-brid-player>
	);
};

BlockSave.propTypes = {
	attributes: PropTypes.shape( {
		autoPlay: PropTypes.bool,
		dataPartner: PropTypes.string,
		dataPlayer: PropTypes.string,
		dataVideo: PropTypes.string,
		dataPlaylist: PropTypes.string,
		dataOutstream: PropTypes.string,
		ampLayout: PropTypes.string,
		width: PropTypes.number,
		height: PropTypes.number,
	} ),
};

export default BlockSave;
