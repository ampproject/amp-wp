/**
 * External dependencies
 */
import Moveable from 'react-moveable';
import styled from 'styled-components';
import PropTypes from 'prop-types';

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

const MovableElement = ( props ) => {
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

	// @todo This is here for forcing Moveable to re-render.
	const [ initPosition, setInitPosition ] = useState( [ x, y ] );

	const {
		actions: { setPropertiesOnSelectedElements, setPropertiesById, selectElementById, toggleElementIdInSelection },
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
		// @todo This is currently for forcing Moveable to re-render with the correct translate values.
		setInitPosition( [ x, y ] );
	};

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
			{ targetEl && <Moveable
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
					const newProps = { x: x + frame.translate[ 0 ], y: y + frame.translate[ 1 ] };
					if ( ! selected ) {
						// Multi-selection is always selected previously,
						// we can use just setting single element properties here.
						selectElementById( id );
						setPropertiesById( id, newProps );
					} else {
						setPropertiesOnSelectedElements( newProps );
					}
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
			/> }
		</>
	);
};

MovableElement.propTypes = {
	rotationAngle: PropTypes.number.isRequired,
	src: PropTypes.string,
	type: PropTypes.string.isRequired,
	selected: PropTypes.bool,
	id: PropTypes.string.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
	rest: PropTypes.object,
};

export default MovableElement;
