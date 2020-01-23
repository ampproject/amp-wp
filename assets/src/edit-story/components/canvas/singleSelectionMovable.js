/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useRef, useEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useStory } from '../../app';
import Movable from '../movable';
import calculateFitTextFontSize from '../../utils/calculateFitTextFontSize';
import getAdjustedElementDimensions from '../../utils/getAdjustedElementDimensions';
import { useUnits } from '../../units';
import useCanvas from './useCanvas';

const ALL_HANDLES = [ 'n', 's', 'e', 'w', 'nw', 'ne', 'sw', 'se' ];

function SingleSelectionMovable( {
	selectedElement,
	targetEl,
	pushEvent,
} ) {
	const moveable = useRef();
	const [ isResizingFromCorner, setIsResizingFromCorner ] = useState( true );

	const { actions: { updateSelectedElements } } = useStory();
	const { actions: { pushTransform } } = useCanvas();
	const { actions: { getBox, editorToDataX, editorToDataY } } = useUnits();

	const latestEvent = useRef();

	useEffect( () => {
		latestEvent.current = pushEvent;
	}, [ pushEvent ] );

	useEffect( () => {
		if ( moveable.current ) {
			// If we have persistent event then let's use that, ensuring the targets match.
			if ( latestEvent.current && targetEl.contains( latestEvent.current.target ) ) {
				moveable.current.moveable.dragStart( latestEvent.current );
			}
			moveable.current.updateRect();
		}
	}, [ targetEl, moveable ] );

	// Update moveable with whatever properties could be updated outside moveable
	// itself.
	useEffect( () => {
		if ( moveable.current ) {
			moveable.current.updateRect();
		}
	} );

	const box = getBox( selectedElement );
	const frame = {
		translate: [ 0, 0 ],
		rotate: box.rotationAngle,
		resize: [ 0, 0 ],
	};

	const setTransformStyle = ( target ) => {
		target.style.transform = `translate(${ frame.translate[ 0 ] }px, ${ frame.translate[ 1 ] }px) rotate(${ frame.rotate }deg)`;
		if ( frame.resize[ 0 ] ) {
			target.style.width = `${ frame.resize[ 0 ] }px`;
		}
		if ( frame.resize[ 1 ] ) {
			target.style.height = `${ frame.resize[ 1 ] }px`;
		}
		pushTransform( selectedElement.id, frame );
	};

	/**
	 * Resets Movable once the action is done, sets the initial values.
	 *
	 * @param {Object} target Target element.
	 */
	const resetMoveable = ( target ) => {
		frame.translate = [ 0, 0 ];
		frame.resize = [ 0, 0 ];
		pushTransform( selectedElement.id, null );
		// Inline start resetting has to be done very carefully here to avoid
		// conflicts with stylesheets. See #3951.
		target.style.transform = '';
		target.style.width = '';
		target.style.height = '';
		setIsResizingFromCorner( true );
		if ( moveable.current ) {
			moveable.current.updateRect();
		}
	};

	const isTextElement = 'text' === selectedElement.type;
	const shouldAdjustFontSize = isTextElement && selectedElement.content.length && isResizingFromCorner;

	return (
		<Movable
			zIndex={ 0 }
			ref={ moveable }
			target={ targetEl }
			draggable={ ! selectedElement.isFullbleed }
			resizable={ ! selectedElement.isFullbleed }
			rotatable={ ! selectedElement.isFullbleed }
			onDrag={ ( { target, beforeTranslate } ) => {
				frame.translate = beforeTranslate;
				setTransformStyle( target );
			} }
			throttleDrag={ 0 }
			onDragStart={ ( { set } ) => {
				set( frame.translate );
			} }
			onDragEnd={ ( { target } ) => {
				// When dragging finishes, set the new properties based on the original + what moved meanwhile.
				if ( frame.translate[ 0 ] !== 0 && frame.translate[ 1 ] !== 0 ) {
					const properties = {
						x: selectedElement.x + editorToDataX( frame.translate[ 0 ] ),
						y: selectedElement.y + editorToDataY( frame.translate[ 1 ] ),
					};
					updateSelectedElements( { properties } );
				}
				resetMoveable( target );
			} }
			onResizeStart={ ( { setOrigin, dragStart, direction } ) => {
				setOrigin( [ '%', '%' ] );
				if ( dragStart ) {
					dragStart.set( frame.translate );
				}
				// Lock ratio for diagonal directions (nw, ne, sw, se). Both
				// `direction[]` values for diagonals are either 1 or -1. Non-diagonal
				// directions have 0s.
				const newResizingMode = direction[ 0 ] !== 0 && direction[ 1 ] !== 0;
				if ( isResizingFromCorner !== newResizingMode ) {
					setIsResizingFromCorner( newResizingMode );
				}
			} }
			onResize={ ( { target, width, height, drag, direction } ) => {
				const isResizingWidth = direction[ 0 ] !== 0 && direction[ 1 ] === 0;
				const isResizingHeight = direction[ 0 ] === 0 && direction[ 1 ] !== 0;
				let newHeight = height;
				let newWidth = width;
				if ( isTextElement && ( isResizingWidth || isResizingHeight ) ) {
					const adjustedDimensions = getAdjustedElementDimensions( {
						element: target,
						content: selectedElement.content,
						width,
						height,
						fixedMeasure: isResizingWidth ? 'width' : 'height',
					} );
					newWidth = adjustedDimensions.width;
					newHeight = adjustedDimensions.height;
				}
				target.style.width = `${ newWidth }px`;
				target.style.height = `${ newHeight }px`;
				frame.resize = [ newWidth, newHeight ];
				frame.translate = drag.beforeTranslate;
				if ( shouldAdjustFontSize ) {
					target.style.fontSize = calculateFitTextFontSize( target.firstChild, height, width );
				}
				setTransformStyle( target );
			} }
			onResizeEnd={ ( { target } ) => {
				if ( frame.resize[ 0 ] !== 0 && frame.resize[ 1 ] !== 0 ) {
					const properties = {
						width: editorToDataX( frame.resize[ 0 ] ),
						height: editorToDataY( frame.resize[ 1 ] ),
						x: selectedElement.x + editorToDataX( frame.translate[ 0 ] ),
						y: selectedElement.y + editorToDataY( frame.translate[ 1 ] ),
					};
					if ( shouldAdjustFontSize ) {
						properties.fontSize = calculateFitTextFontSize( target.firstChild, properties.height, properties.width );
					}
					updateSelectedElements( { properties } );
				}
				resetMoveable( target );
			} }
			onRotateStart={ ( { set } ) => {
				set( frame.rotate );
			} }
			onRotate={ ( { target, beforeRotate } ) => {
				frame.rotate = beforeRotate;
				setTransformStyle( target );
			} }
			onRotateEnd={ ( { target } ) => {
				const properties = { rotationAngle: Math.round( frame.rotate ) };
				updateSelectedElements( { properties } );
				resetMoveable( target );
			} }
			origin={ false }
			pinchable={ true }
			keepRatio={ 'image' === selectedElement.type && isResizingFromCorner }
			renderDirections={ ALL_HANDLES }
		/>
	);
}

SingleSelectionMovable.propTypes = {
	selectedElement: PropTypes.object,
	targetEl: PropTypes.object.isRequired,
	pushEvent: PropTypes.object,
};

export default SingleSelectionMovable;
