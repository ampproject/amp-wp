/**
 * External dependencies
 */
import PropTypes from 'prop-types';

const BlockSave = ( { attributes } ) => {
	const { dataPid, width, height, ampLayout, dataBid, autoPlay, dataBcid, dataVid } = attributes;

	const o2Props = {
		layout: ampLayout,
		height,
		'data-pid': dataPid,
	};
	if ( 'fixed-height' !== ampLayout && width ) {
		o2Props.width = width;
	}
	if ( ! autoPlay ) {
		o2Props[ 'data-macros' ] = 'm.playback=click';
	}
	if ( dataVid ) {
		o2Props[ 'data-vid' ] = dataVid;
	} else if ( dataBcid ) {
		o2Props[ 'data-bcid' ] = dataBcid;
	}
	if ( dataBid ) {
		o2Props[ 'data-bid' ] = dataBid;
	}
	return (
		<amp-o2-player { ...o2Props }></amp-o2-player>
	);
};

BlockSave.propTypes = {
	attributes: PropTypes.shape( {
		autoPlay: PropTypes.bool,
		dataPid: PropTypes.string,
		dataPidL: PropTypes.string,
		dataVid: PropTypes.string,
		dataBcid: PropTypes.string,
		dataBid: PropTypes.string,
		width: PropTypes.number,
		height: PropTypes.number,
		ampLayout: PropTypes.string,
	} ),
};

export default BlockSave;
