/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useRef, useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Movable from '../movable';
import { useStory } from '../../app/story';

function MultiSelectionMovable( { selectedElements, nodesById } ) {
	const moveable = useRef();

	// Update moveable with whatever properties could be updated outside moveable
	// itself.
	useEffect( () => {
		if ( moveable.current ) {
			moveable.current.updateRect();
		}
	}, [ selectedElements, moveable, nodesById ] );

	const { actions: { updateElementsById } } = useStory();

	// Create targets list including nodes and also necessary attributes.
	const targetList = selectedElements.map( ( element ) => {
		return {
			node: nodesById[ element.id ],
			id: element.id,
			x: element.x,
			y: element.y,
			rotationAngle: element.rotationAngle,
		};
	} );
	// Not all targets have been defined yet.
	if ( targetList.some( ( { node } ) => node === undefined ) ) {
		return null;
	}

	/**
	 * Set style to the element.
	 *
	 * @param {Object} target Target element to update.
	 * @param {Object} frame Properties from the frame for that specific element.
	 */
	const setTransformStyle = ( target, frame ) => {
		target.style.transform = `translate(${ frame.translate[ 0 ] }px, ${ frame.translate[ 1 ] }px) rotate(${ frame.rotate }deg)`;
	};

	const frames = targetList ? targetList.map( ( target ) => ( {
		translate: [ 0, 0 ],
		rotate: target.rotationAngle,
	} ) ) : [];

	/**
	 * Resets Movable once the action is done, sets the initial values.
	 */
	const resetMoveable = () => {
		targetList.forEach( ( { node }, i ) => {
			frames[ i ].translate = [ 0, 0 ];
			node.style.transform = '';
			node.style.width = '';
			node.style.height = '';
		} );
		if ( moveable.current ) {
			moveable.current.updateRect();
		}
	};

	const onGroupEventStart = ( { events, isDrag, isRotate } ) => {
		events.forEach( ( ev, i ) => {
			const sFrame = frames[ i ];
			if ( isDrag ) {
				ev.set( sFrame.translate );
			} else if ( isRotate ) {
				ev.set( sFrame.rotate );
			}
		} );
	};

	// Update elements once the event has ended.
	const onGroupEventEnd = ( { targets, isRotate } ) => {
		targets.forEach( ( target, i ) => {
			const properties = {
				x: targetList[ i ].x + frames[ i ].translate[ 0 ],
				y: targetList[ i ].y + frames[ i ].translate[ 1 ],
			};
			if ( isRotate ) {
				properties.rotationAngle = frames[ i ].rotate;
			}
			updateElementsById( { elementIds: [ targetList[ i ].id ], properties } );
		} );
		resetMoveable();
	};

	return (
		<Movable
			ref={ moveable }
			zIndex={ 0 }
			target={ targetList.map( ( { node } ) => node ) }

			// @todo: implement group resizing.
			draggable={ true }
			resizable={ false }
			rotatable={ true }

			onDragGroup={ ( { events } ) => {
				events.forEach( ( { target, beforeTranslate }, i ) => {
					const sFrame = frames[ i ];
					sFrame.translate = beforeTranslate;
					setTransformStyle( target, sFrame );
				} );
			} }
			onDragGroupStart={ ( { events } ) => {
				onGroupEventStart( { events, isDrag: true } );
			} }
			onDragGroupEnd={ ( { targets } ) => {
				onGroupEventEnd( { targets } );
			} }

			onRotateGroupStart={ ( { events } ) => {
				onGroupEventStart( { events, isRotate: true } );
			} }
			onRotateGroup={ ( { events } ) => {
				events.forEach( ( { target, beforeRotate, drag }, i ) => {
					const sFrame = frames[ i ];
					sFrame.rotate = beforeRotate;
					sFrame.translate = drag.beforeTranslate;
					setTransformStyle( target, sFrame );
				} );
			} }
			onRotateGroupEnd={ ( { targets } ) => {
				onGroupEventEnd( { targets, isRotate: true } );
			} }
		/>
	);
}

MultiSelectionMovable.propTypes = {
	selectedElements: PropTypes.arrayOf( PropTypes.object ).isRequired,
	nodesById: PropTypes.object.isRequired,
};

export default MultiSelectionMovable;
