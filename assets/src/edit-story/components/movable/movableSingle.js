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

const CORNER_HANDLES = [ 'nw', 'ne', 'sw', 'se' ];
const ALL_HANDLES = [ 'n', 's', 'e', 'w', 'nw', 'ne', 'sw', 'se' ];

function MovableSingle( {
	selectedEl,
	targetEl,
	pushEvent,
} ) {
	const moveable = useRef();

	const {
		state: { selectedElements },
		actions: { setPropertiesOnSelectedElements },
	} = useStory();

	useEffect( () => {
		if ( moveable.current ) {
			// If we have persistent event then let's use that, ensuring the targets match.
			if ( pushEvent && pushEvent.target === targetEl ) {
				moveable.current.moveable.dragStart( pushEvent );
			}
			moveable.current.updateRect();
		}
		// Disable reason: we should not run this when pushEvent changes.
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ targetEl, moveable ] );

	useEffect( () => {
		if ( moveable.current ) {
			moveable.current.updateRect();
		}
	}, [ selectedElements ] );

	const frame = {
		translate: [ 0, 0 ],
		rotate: selectedEl.rotationAngle,
	};

	/**
	 * Set style to the element.
	 */
	const setStyle = () => {
		targetEl.style.transform = `translate(${ frame.translate[ 0 ] }px, ${ frame.translate[ 1 ] }px) rotate(${ frame.rotate }deg)`;
	};

	/**
	 * Resets Movable once the action is done, sets the initial values.
	 */
	const resetMoveable = () => {
		frame.translate = [ 0, 0 ];
		setStyle();
		if ( moveable.current ) {
			moveable.current.updateRect();
		}
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
				setStyle( target, frame );
			} }
			throttleDrag={ 0 }
			onDragStart={ ( { set } ) => {
				set( frame.translate );
			} }
			onDragEnd={ ( { target } ) => {
				// When dragging finishes, set the new properties based on the original + what moved meanwhile.
				const newProps = { x: selectedEl.x + frame.translate[ 0 ], y: selectedEl.y + frame.translate[ 1 ] };
				setPropertiesOnSelectedElements( newProps );
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
				setStyle( target, frame );
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
				setStyle( target, frame );
			} }
			onRotateEnd={ ( { target } ) => {
				setPropertiesOnSelectedElements( { rotationAngle: frame.rotate } );
				resetMoveable( target );
			} }
			origin={ false }
			pinchable={ true }
			keepRatio={ 'image' === selectedEl.type } // @†odo Even image doesn't always keep ratio, consider moving to element's model.
			renderDirections={ 'image' === selectedEl.type ? CORNER_HANDLES : ALL_HANDLES }
		/>
	);
}

MovableSingle.propTypes = {
	selectedEl: PropTypes.object,
	targetEl: PropTypes.object,
	pushEvent: PropTypes.object,
};

export default MovableSingle;
