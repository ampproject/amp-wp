/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { getCommonAttributes } from '../shared';

/**
 * Returns AMP HTML for saving into post content for displaying in the FE.
 */
function ImageOutput( { id, src, width, height, x, y, rotationAngle, isFullbleed, isPreview } ) {
	const props = {
		layout: 'fill',
		src,
		style: isPreview ? {
			objectFit: isFullbleed ? 'cover' : null,
			width: '100%',
			height: '100%',
		} : null,
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
			{ isPreview ? <img alt={ __( 'Page preview', 'amp' ) } { ...props } /> : <amp-img className={ isFullbleed ? 'full-bleed' : '' } { ...props } /> }
		</div>
	);
}

ImageOutput.propTypes = {
	id: PropTypes.string.isRequired,
	src: PropTypes.string.isRequired,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
	rotationAngle: PropTypes.number.isRequired,
	isFullbleed: PropTypes.bool,
	isPreview: PropTypes.bool,
};

export default ImageOutput;
