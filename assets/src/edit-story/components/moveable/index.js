/**
 * External dependencies
 */
import Moveable from 'react-moveable';
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useCallback, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useStory } from '../../app';
import { getComponentForType } from '../../elements';

const Element = styled.div`
	cursor: pointer;
	user-select: none;
`;

const Movable = ( props ) => {
	const {
		rotationAngle,
		x,
		y,
		type,
		selected,
		id,
		rest,
	} = props;

	const [ targetEl, setTargetEl ] = useState( false );

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
	};

	const {
		actions: { selectElementById, toggleElementIdInSelection },
	} = useStory();

	const handleSelectElement = useCallback( ( elId, evt ) => {
		if ( evt.metaKey ) {
			toggleElementIdInSelection( elId );
		} else {
			selectElementById( elId );
		}
		evt.stopPropagation();
	}, [ toggleElementIdInSelection, selectElementById ] );

	const comp = getComponentForType( type );
	const Comp = comp; // why u do dis, eslint?

	return (
		<>
			<Element
				ref={ setTargetEl }
				key={ id }
				onClick={ ( evt ) => handleSelectElement( id, evt ) }
			>
				<Comp { ...rest } />
			</Element>
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
		</>
	);
};

export default Movable;
