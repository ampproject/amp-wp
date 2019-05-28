/**
 * External dependencies
 */
import PropTypes from 'prop-types';

const BlockSave = ( { attributes } ) => {
	const { width, dataSrc, ampLayout, dataTag, dataDelayAdRequest, height, dataPoster } = attributes;

	const imaProps = {
		layout: ampLayout,
		height,
		width,
		'data-tag': dataTag,
		'data-src': dataSrc,
	};
	if ( dataPoster ) {
		imaProps[ 'data-poster' ] = dataPoster;
	}
	if ( dataDelayAdRequest ) {
		imaProps[ 'data-delay-ad-request' ] = dataDelayAdRequest;
	}
	return (
		<amp-ima-video { ...imaProps }></amp-ima-video>
	);
};

BlockSave.propTypes = {
	attributes: PropTypes.shape( {
		width: PropTypes.number,
		height: PropTypes.number,
		ampLayout: PropTypes.string,
		dataSrc: PropTypes.string,
		dataTag: PropTypes.string,
		dataDelayAdRequest: PropTypes.string,
		dataPoster: PropTypes.string,
	} ),
};

export default BlockSave;
