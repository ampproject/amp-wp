/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { PanelTypes } from '../panels';
import { ElementWithPosition, ElementWithSize, ElementWithRotation } from './shared';

const Element = styled.video`
	${ ElementWithPosition }
	${ ElementWithSize }
	${ ElementWithRotation }
`;

function Video( { src, width, height, x, y, rotationAngle, controls, mimeType } ) {
	const props = {
		width,
		height,
		x,
		y,
		rotationAngle,
		controls,
	};
	return (
		<Element { ...props }>
			<source src={ src } type={ mimeType } />
		</Element>
	);
}

Video.propTypes = {
	rotationAngle: PropTypes.number.isRequired,
	controls: PropTypes.bool,
	mimeType: PropTypes.string.isRequired,
	src: PropTypes.string.isRequired,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
};

Video.defaultProps = {
	controls: true,
};

Video.panels = [
	PanelTypes.SIZE,
	PanelTypes.POSITION,
	PanelTypes.ROTATION_ANGLE,
];

export default Video;
