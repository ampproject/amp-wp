/**
 * External dependencies
 */
import Moveable from 'react-moveable';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useRef, useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useStory } from '../../app';

function MovableGroup( {
	targets: targetList,
} ) {
	const moveable = useRef();
	const {
		state: { selectedElements },
		actions: { updateElementsByIds },
	} = useStory();

	targetList = targetList.filter( ( { id, ref } ) => {
		return null !== ref && selectedElements.filter( ( { id: selectedId } ) => id === selectedId ).length;
	} );

	useEffect( () => {
		if ( moveable.current ) {
			moveable.current.updateRect();
		}
	}, [ selectedElements, moveable ] );

	/**
	 * Set style to the element.
	 *
	 * @param {Object} target Target element to update.
	 * @param {Object} frame Properties from the frame for that specific element.
	 */
	const setStyle = ( target, frame ) => {
		target.style.transform = `translate(${ frame.translate[ 0 ] }px, ${ frame.translate[ 1 ] }px) rotate(${ frame.rotate }deg)`;
	};

	// @todo Is there any time when translate would differ among the elements? If not, we caould use just one translate for these.
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
			setStyle( ref, frames[ i ] );
		} );
		if ( moveable.current ) {
			moveable.current.updateRect();
		}
	};

	return (
		<Moveable
			ref={ moveable }
			target={ targetList.map( ( { ref } ) => ref ) }
			draggable={ true }
			resizable={ false }
			rotatable={ true }
			onDragGroup={ ( { events } ) => {
				events.forEach( ( { target, beforeTranslate }, i ) => {
					const sFrame = frames[ i ];
					sFrame.translate = beforeTranslate;
					setStyle( target, sFrame );
				} );
			} }
			onDragGroupStart={ ( { events } ) => {
				events.forEach( ( ev, i ) => {
					const sFrame = frames[ i ];
					ev.set( sFrame.translate );
				} );
			} }
			onDragGroupEnd={ ( { targets } ) => {
				const updatedElements = [];
				// Set together updated elements.
				targets.forEach( ( target, i ) => {
					// @todo Improve this here.
					updatedElements.push( { id: targetList[ i ].id, x: targetList[ i ].x + frames[ i ].translate[ 0 ], y: targetList[ i ].y + frames[ i ].translate[ 1 ] } );
				} );
				if ( updatedElements.length ) {
					// Update the elements.
					updateElementsByIds( updatedElements );
				}
				resetMoveable( null );
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
					setStyle( target, sFrame );
				} );
			} }
			onRotateGroupEnd={ ( { targets } ) => {
				const updatedElements = [];
				// Set together updated elements.
				targets.forEach( ( target, i ) => {
					updatedElements.push( {
						id: targetList[ i ].id,
						x: targetList[ i ].x + frames[ i ].translate[ 0 ],
						y: targetList[ i ].y + frames[ i ].translate[ 1 ],
						rotationAngle: frames[ i ].rotate,
					} );
				} );
				if ( updatedElements.length ) {
					// Update the elements.
					updateElementsByIds( updatedElements );
				}
				resetMoveable( null );
			} }
			origin={ false }
		/>
	);
}

MovableGroup.propTypes = {
	targets: PropTypes.array,
};

export default MovableGroup;
