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
function ImageSave( { id, src, width, height, x, y, rotationAngle, isFullbleed } ) {
	const props = {
		layout: 'fill',
		src,
	};
	const wrapperProps = {
		id: 'el-' + id,
	};
	const style = getCommonAttributes( { width, height, x, y, rotationAngle } );
	// @todo This is missing focal point handling which will be resolved separately.
	if ( isFullbleed ) {
		style.top = 0;
		style.left = 0;
		style.width = '100%';
		style.height = '100%';
	}

	return (
		<div style={ { ...style } } { ...wrapperProps }>
			<amp-img className={ isFullbleed ? 'full-bleed' : '' } { ...props } />
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
	isFullbleed: PropTypes.bool,
};

export default ImageSave;
