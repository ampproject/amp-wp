/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useCallback, useLayoutEffect, useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useCanvas } from '../../components/canvas';
import useDoubleClick from '../../utils/useDoubleClick';
import { ElementWithPosition, ElementWithSize, ElementWithRotation, getBox } from '../shared';
import { getImgProps, ImageWithScale } from './util';

const Element = styled.div`
	${ ElementWithPosition }
	${ ElementWithSize }
	${ ElementWithRotation }
	overflow: hidden;
`;

const Img = styled.img`
	position: absolute;
	${ ImageWithScale }
`;

function ImageDisplay( { id, src, origRatio, width, height, x, y, scale, focalX, focalY, rotationAngle, isFullbleed, forwardedRef, onPointerDown, setTargetRefs } ) {
	const element = useRef();
	const elementProps = {
		...getBox( { x, y, width, height, rotationAngle, isFullbleed } ),
		ref: forwardedRef ? forwardedRef : element,
		onPointerDown,
	};

	useLayoutEffect( () => {
		setTargetRefs( ( targets ) => {
			const hasId = Boolean( targets.filter( ( { id: existingId } ) => id === existingId ).length );
			if ( ! hasId ) {
				targets.push( {
					id,
					ref: element.current,
					x,
					y,
					rotationAngle,
				} );
			}
			return targets;
		} );
	}, [ id, setTargetRefs, forwardedRef ] );

	const imgProps = getImgProps( elementProps.width, elementProps.height, scale, focalX, focalY, origRatio );
	const {
		actions: { setEditingElement },
	} = useCanvas();
	const handleSingleClick = useCallback( () => {}, [] );
	const handleDoubleClick = useCallback( () => setEditingElement( id ), [ id, setEditingElement ] );
	const getHandleClick = useDoubleClick( handleSingleClick, handleDoubleClick );
	return (
		<Element { ...elementProps } onClick={ getHandleClick( id ) }>
			<Img draggable={ false } src={ src } { ...imgProps } />
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
	isFullbleed: PropTypes.bool,
	focalX: PropTypes.number,
	focalY: PropTypes.number,
	forwardedRef: PropTypes.oneOfType( [
		PropTypes.object,
		PropTypes.func,
	] ),
	onPointerDown: PropTypes.func,
};

ImageDisplay.defaultProps = {
	scale: null,
	focalX: null,
	focalY: null,
};

export default ImageDisplay;
