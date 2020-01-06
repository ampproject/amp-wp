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

function MovableSingle( {
	selectedEl,
	targetEl,
	pushEvent,
} ) {
	const moveable = useRef();
	const [ keepRatioMode, setKeepRatioMode ] = useState( true );
	// Update moveable with whatever properties could be updated outside moveable
	// itself.
	useEffect( () => {
		if ( moveable.current ) {
			moveable.current.updateRect();
		}
	} );

	const {
		state: { selectedElements },
		actions: { setPropertiesOnSelectedElements },
	} = useStory();

	const latestEvent = useRef();

	useEffect( () => {
		latestEvent.current = pushEvent;
	}, [ pushEvent ] );

	useEffect( () => {
		if ( moveable.current ) {
			// If we have persistent event then let's use that, ensuring the target is correct.
			if ( latestEvent.current && targetEl.contains( latestEvent.current.target ) ) {
				moveable.current.moveable.dragStart( latestEvent.current );
			}
			moveable.current.updateRect();
		}
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
	const setTransformStyle = () => {
		targetEl.style.transform = `translate(${ frame.translate[ 0 ] }px, ${ frame.translate[ 1 ] }px) rotate(${ frame.rotate }deg)`;
	};

	/**
	 * Resets Movable once the action is done, sets the initial values.
	 */
	const resetMoveable = () => {
		frame.translate = [ 0, 0 ];
		// Inline start resetting has to be done very carefully here to avoid
		// conflicts with stylesheets. See #3951.
		targetEl.style.transform = '';
		targetEl.style.width = '';
		targetEl.style.height = '';
		setKeepRatioMode( true );
		if ( moveable.current ) {
			moveable.current.updateRect();
		}
	};

	return (
		<Moveable
			ref={ moveable }
			target={ targetEl }
			draggable={ ! selectedEl.isFullbleed }
			resizable={ ! selectedEl.isFullbleed }
			rotatable={ ! selectedEl.isFullbleed }
			onDrag={ ( { target, beforeTranslate } ) => {
				frame.translate = beforeTranslate;
				setTransformStyle( target, frame );
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
				setTransformStyle( target, frame );
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
				setTransformStyle( target, frame );
			} }
			onRotateEnd={ ( { target } ) => {
				setPropertiesOnSelectedElements( { rotationAngle: frame.rotate } );
				resetMoveable( target );
			} }
			origin={ false }
			pinchable={ true }
			keepRatio={ 'image' === selectedEl.type && keepRatioMode }
			renderDirections={ ALL_HANDLES }
		/>
	);
}

MovableSingle.propTypes = {
	selectedEl: PropTypes.object,
	targetEl: PropTypes.object,
	pushEvent: PropTypes.object,
};

export default MovableSingle;
