/**
 * External dependencies
 */
import Moveable from 'react-moveable';

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useStory } from '../../app';

const Movable = ( props ) => {
	const {
		targetEl,
		rotationAngle,
		x,
		y,
		type,
		selected,
	} = props;

	const [ updated, setUpdated ] = useState( false );

	const {
		actions: { setPropertiesOnSelectedElements },
	} = useStory();

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
		setUpdated( true );
	};

	return (
		<Moveable
			className={ selected ? 'selected' : null }
			target={ targetEl.firstChild }
			draggable={ true }
			resizable={ selected }
			rotatable={ selected }
			onDrag={ ( { target, beforeTranslate } ) => {
				frame.translate = beforeTranslate;
				setStyle( target );
			} }
			onDragStart={ ( { set } ) => {
				set( frame.translate );
			} }
			onDragEnd={ ( { target } ) => {
				setPropertiesOnSelectedElements( { x: x + frame.translate[ 0 ], y: y + frame.translate[ 1 ] } );
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

export default Movable;
