/**
 * External dependencies
 */
import Moveable from 'react-moveable';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useEffect, useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useStory } from '../../app';

function Movable( {
	selectedEl,
	targetEl,
	targets: targetList,
} ) {
	const {
		state: { selectedElements },
		actions: { setPropertiesOnSelectedElements, updateElementsByIds },
	} = useStory();

	const moveable = useRef();

	useEffect( () => {
		if ( moveable.current ) {
			moveable.current.updateRect();
		}
	}, [ selectedElements ] );

	const setStyle = ( target ) => {
		target.style.transform = `translate(${ frame.translate[ 0 ] }px, ${ frame.translate[ 1 ] }px) rotate(${ frame.rotate }deg)`;
	};

	const frames = targetList ? targetList.map( () => ( {
		translate: [ 0, 0 ],
		rotationAngle: 0,
	} ) ) : [];

	const resetMoveable = ( target ) => {
		// @todo Improve this logic.
		if ( targetList && targetList.length ) {
			targetList.forEach( ( el, i ) => {
				frames[ i ].translate = [ 0, 0 ];
				el.style.transform = `translate(0px, 0px) rotate(0deg)`;
			} );
		} else {
			frame.translate = [ 0, 0 ];
			setStyle( target );
		}
		if ( moveable.current ) {
			moveable.current.updateRect();
		}
	};

	if ( targetList && targetList.length ) {
		return (
			<Moveable
				ref={ moveable }
				target={ targetList }
				draggable={ true }
				resizable={ false }
				rotatable={ true }
				onDragGroup={ ( { events } ) => {
					events.forEach( ( { target, beforeTranslate }, i ) => {
						const sFrame = frames[ i ];
						sFrame.translate = beforeTranslate;
						target.style.transform = `translate(${ beforeTranslate[ 0 ] }px, ${ beforeTranslate[ 1 ] }px)`;
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
						updatedElements.push( { id: selectedElements[ i ].id, x: selectedElements[ i ].x + frames[ i ].translate[ 0 ], y: selectedElements[ i ].y + frames[ i ].translate[ 1 ] } );
					} );
					if ( updatedElements.length ) {
						// Update the elements.
						updateElementsByIds( updatedElements );
					}
					resetMoveable( null );
				} }
				origin={ false }
				pinchable={ true }
			/>
		);
	}

	const frame = {
		translate: [ 0, 0 ],
		rotate: selectedEl.rotationAngle,
	};

	return (
		<Moveable
			ref={ moveable }
			target={ targetEl }
			draggable={ true }
			resizable={ true }
			rotatable={ true }
			onDrag={ ( { target, beforeTranslate } ) => {
				frame.translate = beforeTranslate;
				setStyle( target );
			} }
			onDragStart={ ( { set } ) => {
				set( frame.translate );
			} }
			onDragEnd={ ( { target } ) => {
				setPropertiesOnSelectedElements( { x: selectedEl.x + frame.translate[ 0 ], y: selectedEl.y + frame.translate[ 1 ] } );
				resetMoveable( target );
			} }
			onResizeStart={ ( { setOrigin, dragStart } ) => {
				setOrigin( [ '%', '%' ] );
				if ( dragStart ) {
					dragStart.set( frame.translate );
				}
			} }
			onResize={ ( { target, width, height, drag } ) => {
				target.style.width = `${ width }px`;
				target.style.height = `${ height }px`;
				frame.translate = drag.beforeTranslate;
				setStyle( target );
			} }
			onResizeEnd={ ( { target } ) => {
				setPropertiesOnSelectedElements( {
					width: parseInt( target.style.width ),
					height: parseInt( target.style.height ),
					x: selectedEl.x + frame.translate[ 0 ],
					y: selectedEl.y + frame.translate[ 1 ],
				} );
				resetMoveable( target );
			} }
			onRotateStart={ ( { set } ) => {
				set( frame.rotate );
			} }
			onRotate={ ( { target, beforeRotate } ) => {
				frame.rotate = beforeRotate;
				setStyle( target );
			} }
			onRotateEnd={ ( { target } ) => {
				setPropertiesOnSelectedElements( { rotationAngle: frame.rotate } );
				resetMoveable( target );
			} }
			origin={ false }
			pinchable={ true }
			keepRatio={ 'image' === selectedElements[ 0 ].type }
			renderDirections={ 'image' === selectedElements[ 0 ].type ? [ 'nw', 'ne', 'sw', 'se' ] : [ 'n', 's', 'e', 'w', 'nw', 'ne', 'sw', 'se' ] }
		/>
	);
}

Movable.propTypes = {
	selectedEl: PropTypes.object,
	targetEl: PropTypes.object,
	targets: PropTypes.array,
};

export default Movable;
