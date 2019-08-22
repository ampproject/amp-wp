/**
 * External dependencies
 */
import moment from 'moment';
import PropTypes from 'prop-types';

const BlockSave = ( { attributes } ) => {
	const { ampLayout, width, height, align, cutoff, dateTime } = attributes;

	const timeagoProps = {
		layout: 'responsive',
		className: 'align' + ( align || 'none' ),
		datetime: dateTime,
		locale: 'en',
	};
	if ( cutoff ) {
		timeagoProps.cutoff = cutoff;
	}
	if ( ampLayout ) {
		switch ( ampLayout ) {
			case 'fixed-height':
				if ( height ) {
					timeagoProps.height = height;
					timeagoProps.layout = ampLayout;
				}
				break;
			case 'fixed':
				if ( height && width ) {
					timeagoProps.height = height;
					timeagoProps.width = width;
					timeagoProps.layout = ampLayout;
				}
				break;

			default:
				break;
		}
	}
	return (
		<amp-timeago { ...timeagoProps }>{ moment( attributes.dateTime ).format( 'dddd D MMMM HH:mm' ) }</amp-timeago>
	);
};

BlockSave.propTypes = {
	attributes: PropTypes.shape( {
		width: PropTypes.number,
		height: PropTypes.number,
		ampLayout: PropTypes.string,
		align: PropTypes.string,
		cutoff: PropTypes.number,
		dateTime: PropTypes.string,
	} ),
};

export default BlockSave;
