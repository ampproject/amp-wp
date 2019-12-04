/**
 * External dependencies
 */
import styled from 'styled-components';
import Moveable from 'react-moveable';

/**
 * WordPress dependencies
 */
import { useCallback, useEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useStory } from '../../app';
import { getDefinitionForType } from '../../elements';
import useCanvas from './useCanvas';

const Background = styled.div.attrs( { className: 'container' } )`
	background-color: ${ ( { theme } ) => theme.colors.fg.v1 };
	position: relative;
	width: 100%;
	height: 100%;
`;

const Selection = styled.div`
	z-index: 2;
	left: ${ ( { x } ) => `${ x }px` };
	top: ${ ( { y } ) => `${ y }px` };
	width: ${ ( { width } ) => `${ width }px` };
	height: ${ ( { height } ) => `${ height }px` };
	transform: ${ ( { rotationAngle } ) => `rotate(${ rotationAngle }deg)` };
	position: absolute;
`;

const Element = styled.div`
	${ ( { isPassive } ) => isPassive ? 'opacity: .4;' : 'cursor: pointer;' }
`;

function Page() {
	const [ targetEl, setTargetEl ] = useState( null );

	const {
		state: { currentPage, hasSelection, selectedElements },
		actions: { clearSelection, selectElementById, setPropertiesOnSelectedElements, toggleElementIdInSelection },
	} = useStory();
	const {
		state: { isEditing, editingElement },
		actions: { setBackgroundClickHandler },
	} = useCanvas();
	const handleSelectElement = useCallback( ( id, evt ) => {
		if ( evt.metaKey ) {
			toggleElementIdInSelection( id );
		} else {
			selectElementById( id );
		}
		evt.stopPropagation();
	}, [ toggleElementIdInSelection, selectElementById ] );
	const selectionProps = hasSelection ? getUnionSelection( selectedElements, 0 ) : {};
	useEffect( () => {
		setBackgroundClickHandler( () => clearSelection() );
	}, [ setBackgroundClickHandler, clearSelection ] );

	const displayMoveable = selectedElements.length === 1;
	const frame = {
		translate: [ 0, 0 ],
		rotate: selectionProps.rotationAngle,
	};

	const setStyle = ( target ) => {
		target.style.transform = `translate(${ frame.translate[ 0 ] }px, ${ frame.translate[ 1 ] }px) rotate(${ frame.rotate }deg)`;
	};

	return (
		<Background>
			{ currentPage && currentPage.elements.map( ( { type, ...rest } ) => {
				const { Display, Edit } = getDefinitionForType( type );
				const { id } = rest;

				// Are we editing this element, display this as Edit component.
				if ( editingElement === id ) {
					return (
						<Element key={ id }>
							<Edit { ...rest } />
						</Element>
					);
				}

				// Are we editing some other element, display this as passive Display.
				if ( isEditing ) {
					return (
						<Element key={ id } isPassive>
							<Display { ...rest } />
						</Element>
					);
				}

				return (
					<Element key={ id } onClick={ ( evt ) => handleSelectElement( id, evt ) }>
						<Display { ...rest } />
					</Element>
				);
			} ) }

			{ hasSelection && ! isEditing && (
				<Selection { ...selectionProps } ref={ setTargetEl } />
			) }
			{ displayMoveable && targetEl && (
				<Moveable
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
					onDragEnd={ () => {
						setPropertiesOnSelectedElements( { x: selectionProps.x + frame.translate[ 0 ], y: selectionProps.y + frame.translate[ 1 ] } );
					} }
					onResizeStart={ ( { setOrigin, dragStart } ) => {
						setOrigin( [ '%', '%' ] );
						if ( dragStart ) {
							dragStart.set( frame.translate );
						}
					} }
					onResize={ ( { target, width, height, drag } ) => {
						// @todo This is sliding slightly while resizing an image element specifically, needs looking into.
						target.style.width = `${ width }px`;
						target.style.height = `${ height }px`;
						frame.translate = drag.beforeTranslate;
						setStyle( target );
					} }
					onResizeEnd={ ( { target } ) => {
						setPropertiesOnSelectedElements( {
							width: parseInt( target.style.width ),
							height: parseInt( target.style.height ),
							x: selectionProps.x + frame.translate[ 0 ],
							y: selectionProps.y + frame.translate[ 1 ],
						} );
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
					keepRatio={ 'image' === selectedElements[ 0 ].type }
					renderDirections={ 'image' === selectedElements[ 0 ].type ? [ 'nw', 'ne', 'sw', 'se' ] : [ 'n', 's', 'e', 'w', 'nw', 'ne', 'sw', 'se' ] }
				/>
			) }
		</Background>
	);
}

export default Page;

function getUnionSelection( list, padding = 0 ) {
	// Ignore multi-selection for now.
	if ( 1 === list.length ) {
		const { x, y, width, height, rotationAngle } = list[ 0 ];
		return {
			x: x - padding,
			y: y - padding,
			width: width + ( 2 * padding ),
			height: height + ( 2 * padding ),
			rotationAngle,
		};
	}
	// return x,y,width,height that will encompass all elements in list
	const { x1, y1, x2, y2, rotationAngle } = list
	// first convert x1,y1 as upper left and x2,y2 as lower right
		.map( ( { x, y, width, height } ) => ( { x1: x, y1: y, x2: x + width, y2: y + height } ) )
		// then reduce to a single object by finding lowest {x,y}1 and highest {x,y}2
		.reduce(
			( sum, el ) => ( {
				x1: Math.min( el.x1, sum.x1 ),
				y1: Math.min( el.y1, sum.y1 ),
				x2: Math.max( el.x2, sum.x2 ),
				y2: Math.max( el.y2, sum.y2 ),
			} ),
			{ x1: Number.MAX_VALUE, y1: Number.MAX_VALUE, x2: 0, y2: 0, angle: 0 },
		);

	// finally convert back to x,y,width,height and add padding
	return { x: x1 - padding, y: y1 - padding, width: x2 - x1 + ( 2 * padding ), height: y2 - y1 + ( 2 * padding ), rotationAngle };
}
