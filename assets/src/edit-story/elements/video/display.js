/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { ElementWithPosition, ElementWithSize, ElementWithRotation } from '../shared';

const Element = styled.video`
	${ ElementWithPosition }
	${ ElementWithSize }
	${ ElementWithRotation }
`;

function VideoDisplay( props ) {
	const {
		mimeType,
		src,
	} = props;

	return (
		<Element { ...props } >
			<source src={ src } type={ mimeType } />
		</Element>
	);
}

VideoDisplay.propTypes = {
	rotationAngle: PropTypes.number.isRequired,
	controls: PropTypes.bool,
	autoPlay: PropTypes.bool,
	loop: PropTypes.bool,
	mimeType: PropTypes.string.isRequired,
	src: PropTypes.string.isRequired,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
};

export default VideoDisplay;
