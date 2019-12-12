/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { PAGE_WIDTH, PAGE_HEIGHT } from '../../constants';
import { useCanvas } from '../../components/canvas';
import useDoubleClick from '../../utils/useDoubleClick';
import { ElementWithPosition, ElementWithSize, ElementWithRotation } from '../shared';
import { getImgProps, ImageWithScale } from './util';

const Element = styled.div`
	${ ElementWithPosition }
	${ ElementWithSize }
	${ ElementWithRotation }
	overflow: hidden;
`;

const Img = styled.img`
	position: relative;
	${ ImageWithScale }
`;

function ImageDisplay( { id, src, origRatio, width, height, x, y, scale, offsetX, offsetY, rotationAngle, forwardedRef, onPointerDown } ) {
	// Width and height are percent of the actual page dimensions,
	// Thus 20-by-20 doesn't mean square, but "same as page ratio".
	const actualRatio = width / height * PAGE_WIDTH / PAGE_HEIGHT;
	const imgProps = getImgProps( scale, offsetX, offsetY, origRatio, actualRatio );
	const elementProps = {
		width,
		height,
		x,
		y,
		rotationAngle,
		ref: forwardedRef,
		onPointerDown,
	};
	const {
		actions: { setEditingElement },
	} = useCanvas();
	const handleSingleClick = useCallback( () => {}, [] );
	const handleDoubleClick = useCallback( () => setEditingElement( id ), [ id, setEditingElement ] );
	const getHandleClick = useDoubleClick( handleSingleClick, handleDoubleClick );
	return (
		<Element { ...elementProps } onClick={ getHandleClick( id ) }>
			<Img src={ src } { ...imgProps } />
		</Element>
	);
}

ImageDisplay.propTypes = {
	id: PropTypes.string.isRequired,
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
	forwardedRef: PropTypes.oneOfType( [
		PropTypes.object,
		PropTypes.func,
	] ),
	onPointerDown: PropTypes.func,
};

ImageDisplay.defaultProps = {
	scale: null,
	offsetX: null,
	offsetY: null,
};

export default ImageDisplay;
