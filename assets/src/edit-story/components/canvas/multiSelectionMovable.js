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
			ref: nodesById[ element.id ],
			id: element.id,
			x: element.x,
			y: element.y,
			rotationAngle: element.rotationAngle,
		};
	} );
	// Not all targets have been defined yet.
	if ( targetList.some( ( { ref } ) => ref === undefined ) ) {
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
		targetList.forEach( ( { ref }, i ) => {
			frames[ i ].translate = [ 0, 0 ];
			ref.style.transform = '';
			ref.style.width = '';
			ref.style.height = '';
		} );
		if ( moveable.current ) {
			moveable.current.updateRect();
		}
	};

	return (
		<Movable
			ref={ moveable }
			zIndex={ 0 }
			target={ targetList.map( ( { ref } ) => ref ) }

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
				events.forEach( ( ev, i ) => {
					const sFrame = frames[ i ];
					ev.set( sFrame.translate );
				} );
			} }
			onDragGroupEnd={ ( { targets } ) => {
				targets.forEach( ( target, i ) => {
					const properties = { x: targetList[ i ].x + frames[ i ].translate[ 0 ], y: targetList[ i ].y + frames[ i ].translate[ 1 ] };
					updateElementsById( { elementIds: [ targetList[ i ].id ], properties } );
				} );
				resetMoveable();
			} }

			onRotateGroupStart={ ( { events } ) => {
				events.forEach( ( ev, i ) => {
					const sFrame = frames[ i ];
					ev.set( sFrame.rotate );
				} );
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
				// Set together updated elements.
				targets.forEach( ( target, i ) => {
					const properties = {
						x: targetList[ i ].x + frames[ i ].translate[ 0 ],
						y: targetList[ i ].y + frames[ i ].translate[ 1 ],
						rotationAngle: frames[ i ].rotate,
					};
					updateElementsById( { elementIds: [ targetList[ i ].id ], properties } );
				} );
				resetMoveable();
			} }
		/>
	);
}

MultiSelectionMovable.propTypes = {
	selectedElements: PropTypes.arrayOf( PropTypes.object ).isRequired,
	nodesById: PropTypes.object.isRequired,
};

export default MultiSelectionMovable;
