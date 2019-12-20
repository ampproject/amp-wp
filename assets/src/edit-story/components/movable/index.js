/**
 * External dependencies
 */
import Moveable from 'react-moveable';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useRef, useEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useStory } from '../../app';

const ALL_HANDLES = [ 'n', 's', 'e', 'w', 'nw', 'ne', 'sw', 'se' ];

function Movable( {
	selectedElement,
	targetEl,
	pushEvent,
} ) {
	const moveable = useRef();
	const [ keepRatioMode, setKeepRatioMode ] = useState( true );

	const {
		actions: { setPropertiesOnSelectedElements },
	} = useStory();

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

	const frame = {
		translate: [ 0, 0 ],
		rotate: selectedElement.rotationAngle,
	};

	const setStyle = ( target ) => {
		target.style.transform = `translate(${ frame.translate[ 0 ] }px, ${ frame.translate[ 1 ] }px) rotate(${ frame.rotate }deg)`;
	};

	/**
	 * Resets Movable once the action is done, sets the initial values.
	 *
	 * @param {Object} target Target element.
	 */
	const resetMoveable = ( target ) => {
		frame.translate = [ 0, 0 ];
		setStyle( target );
		target.style.width = '';
		target.style.height = '';
		setKeepRatioMode( true );
		if ( moveable.current ) {
			moveable.current.updateRect();
		}
	};

	return (
		<Moveable
			ref={ moveable }
			target={ targetEl }
			draggable={ ! selectedElement.isFullbleed }
			resizable={ ! selectedElement.isFullbleed }
			rotatable={ ! selectedElement.isFullbleed }
			onDrag={ ( { target, beforeTranslate } ) => {
				frame.translate = beforeTranslate;
				setStyle( target );
			} }
			throttleDrag={ 0 }
			onDragStart={ ( { set } ) => {
				set( frame.translate );
			} }
			onDragEnd={ ( { target } ) => {
				// When dragging finishes, set the new properties based on the original + what moved meanwhile.
				const newProps = { x: selectedElement.x + frame.translate[ 0 ], y: selectedElement.y + frame.translate[ 1 ] };
				setPropertiesOnSelectedElements( newProps );
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
				const newKeepRatioMode = direction[ 0 ] !== 0 && direction[ 1 ] !== 0;
				if ( keepRatioMode !== newKeepRatioMode ) {
					setKeepRatioMode( newKeepRatioMode );
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
					x: selectedElement.x + frame.translate[ 0 ],
					y: selectedElement.y + frame.translate[ 1 ],
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
			onRotateEnd={ () => {
				setPropertiesOnSelectedElements( { rotationAngle: frame.rotate } );
			} }
			origin={ false }
			pinchable={ true }
			keepRatio={ 'image' === selectedElement.type && keepRatioMode }
			renderDirections={ ALL_HANDLES }
		/>
	);
}

Movable.propTypes = {
	selectedElement: PropTypes.object,
	targetEl: PropTypes.object.isRequired,
	pushEvent: PropTypes.object,
};

export default Movable;
