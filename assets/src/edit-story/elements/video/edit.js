/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { PAGE_WIDTH, PAGE_HEIGHT } from '../../constants';
import { ElementWithPosition, ElementWithSize, ElementWithRotation } from '../shared';
import { getVideoProps, VideoWithScale } from './util';

const Element = styled.div`
	${ ElementWithPosition }
	${ ElementWithSize }
	${ ElementWithRotation }
`;

const ActualBox = styled.div`
	width: 100%;
	height: 100%;
	overflow: hidden;

	&::after {
		content: '';
		display: block;
		position: absolute;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		border: 1px solid ${ ( { theme } ) => theme.colors.mg.v1 }70;
		pointer-events: none;
	}
`;

const FadedVideo = styled.video`
	position: absolute;
	opacity: 0.4;
	${ VideoWithScale }
`;

const ActualVideo = styled.video`
	position: relative;
	${ VideoWithScale }
`;

function VideoEdit( { src, origRatio, width, height, x, y, scale, offsetX, offsetY, rotationAngle } ) {
	const actualRatio = width / height * PAGE_WIDTH / PAGE_HEIGHT;
	const videoProps = getVideoProps( scale, offsetX, offsetY, origRatio, actualRatio );
	const elementProps = {
		width,
		height,
		x,
		y,
		rotationAngle,
	};
	return (
		<Element { ...elementProps }>
			<FadedVideo src={ src } { ...videoProps } />
			<ActualBox>
				<ActualVideo src={ src } { ...videoProps } />
			</ActualBox>
		</Element>
	);
}

VideoEdit.propTypes = {
	src: PropTypes.string.isRequired,
	origRatio: PropTypes.number.isRequired,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
	scale: PropTypes.number,
	rotationAngle: PropTypes.number.isRequired,
	offsetX: PropTypes.number,
	offsetY: PropTypes.number,
	videoCaption: PropTypes.string,
	ampAriaLabel: PropTypes.string,
};

VideoEdit.defaultProps = {
	scale: null,
	offsetX: null,
	offsetY: null,
	videoCaption: '',
	ampAriaLabel: '',
};

export default VideoEdit;
