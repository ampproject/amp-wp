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

const Movable = ( props ) => {
	const {
		rotationAngle,
		x,
		y,
		type,
		targetEl,
		pushEvent,
	} = props;

	const moveable = useRef();

	const {
		actions: { setPropertiesOnSelectedElements },
	} = useStory();

	useEffect( () => {
		if ( moveable.current ) {
			if ( pushEvent ) {
				moveable.current.moveable.dragStart( pushEvent );
			}
			moveable.current.updateRect();
		}
	}, [ targetEl, moveable, pushEvent ] );

	const frame = {
		translate: [ 0, 0 ],
		rotate: rotationAngle,
	};

	const setStyle = ( target ) => {
		target.style.transform = `translate(${ frame.translate[ 0 ] }px, ${ frame.translate[ 1 ] }px) rotate(${ frame.rotate }deg)`;
	};

	const resetMoveable = ( target ) => {
		frame.translate = [ 0, 0 ];
		setStyle( target );
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
				setStyle( target );
			} }
			throttleDrag={ 0 }
			onDragStart={ ( { set } ) => {
				set( frame.translate );
			} }
			onDragEnd={ ( { target } ) => {
				const newProps = { x: x + frame.translate[ 0 ], y: y + frame.translate[ 1 ] };
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
				setStyle( target );
			} }
			onResizeEnd={ ( { target } ) => {
				setPropertiesOnSelectedElements( {
					width: parseInt( target.style.width ),
					height: parseInt( target.style.height ),
					x: x + frame.translate[ 0 ],
					y: y + frame.translate[ 1 ],
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
			keepRatio={ 'image' === type }
			renderDirections={ 'image' === type ? [ 'nw', 'ne', 'sw', 'se' ] : [ 'n', 's', 'e', 'w', 'nw', 'ne', 'sw', 'se' ] }
		/>
	);
};

Movable.propTypes = {
	rotationAngle: PropTypes.number.isRequired,
	targetEl: PropTypes.object.isRequired,
	type: PropTypes.string.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
};

export default Movable;
