/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { getCommonAttributes } from '../shared';

/**
 * Returns AMP HTML for saving into post content for displaying in the FE.
 */
function ImageSave( { id, src, width, height, x, y, rotationAngle } ) {
	const props = {
		layout: 'fill',
		src,
	};
	const wrapperProps = {
		id: 'el-' + id,
	};
	const style = getCommonAttributes( { width, height, x, y, rotationAngle } );

	return (
		<div style={ { ...style } } { ...wrapperProps }>
			<amp-img { ...props } />
		</div>
	);
}

ImageSave.propTypes = {
	id: PropTypes.string.isRequired,
	src: PropTypes.string.isRequired,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
	rotationAngle: PropTypes.number.isRequired,
};

export default ImageSave;
