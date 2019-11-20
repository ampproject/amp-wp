/**
 * External dependencies
 */
import styled from 'styled-components';
import Moveable from 'react-moveable';

/**
 * WordPress dependencies
 */
import { useCallback, useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useStory } from '../../app';
import { getComponentForType } from '../../elements';
import useCanvas from './useCanvas';

const Background = styled.div`
	background-color: ${ ( { theme } ) => theme.colors.fg.v1 };
	position: relative;
	width: 100%;
	height: 100%;
`;

const Selection = styled.div`
	z-index: 2;
	border: 1px solid #448FFF;
	left: ${ ( { x } ) => `${ x }%` };
	top: ${ ( { y } ) => `${ y }%` };
	width: ${ ( { width } ) => `${ width }%` };
	height: ${ ( { height } ) => `${ height }%` };
	position: absolute;
	pointer-events:  none;
`;

const Element = styled.div`
	cursor: pointer;
	user-select: none;
`;

function Page() {
	const {
		state: { currentPage, hasSelection, selectedElements },
		actions: { clearSelection, selectElementById, toggleElementIdInSelection },
	} = useStory();
	const {
		actions: { setBackgroundClickHandler },
	} = useCanvas();
	const handleSelectElement = useCallback( ( id ) => ( evt ) => {
		if ( evt.metaKey ) {
			toggleElementIdInSelection( id );
		} else {
			selectElementById( id );
		}
		evt.stopPropagation();
	}, [ toggleElementIdInSelection, selectElementById ] );
	const selectionProps = hasSelection ? getUnionSelection( selectedElements, 2 ) : {};
	useEffect( () => {
		setBackgroundClickHandler( () => clearSelection() );
	}, [ setBackgroundClickHandler, clearSelection ] );
	let selector;
	return (
		<Background>
			{ currentPage && currentPage.elements.map( ( { type, id, ...rest } ) => {
				const comp = getComponentForType( type );
				const Comp = comp; // why u do dis, eslint?
				selector = id; // @todo We should actually assign the selector only if the specific block is selected, too.
				return (
					<Element id={ 'element-' + id } key={ id } onClick={ handleSelectElement( id ) }>
						<Comp { ...rest } />
					</Element>
				);
			} ) }
			{ hasSelection && (
				<Selection { ...selectionProps } />
			) }
			{ hasSelection && selector && (
				<Moveable
					target={ document.querySelector( '#element-' + selector + ' > :first-child' ) }
					pinchThreshold={ 20 }
					draggable={ true }
					resizable={ true }
					rotatable={ true }
					onDrag={ ( { target, top, left } ) => {
						target.style.left = `${ left }px`;
						target.style.top = `${ top }px`;
					} }
					onResize={ ( { target, width, height } ) => {
						target.style.width = `${ width }px`;
						target.style.height = `${ height }px`;
					} }
					onRotate={ ( { target, transform } ) => {
						target.style.transform = transform;
					} }
				/>
			) }
		</Background>
	);
}

export default Page;

function getUnionSelection( list, padding = 0 ) {
	// return x,y,width,height that will encompass all elements in list
	const { x1, y1, x2, y2 } = list
		// first convert x1,y1 as upper left and x2,y2 as lower right
		.map( ( { x, y, width, height } ) => ( { x1: x, y1: y, x2: x + width, y2: y + height } ) )
		// then reduce to a single object by finding lowest {x,y}1 and highest {x,y}2
		.reduce(
			( el, sum ) => ( {
				x1: Math.min( el.x1, sum.x1 ),
				y1: Math.min( el.y1, sum.y1 ),
				x2: Math.max( el.x2, sum.x2 ),
				y2: Math.max( el.y2, sum.y2 ),
			} ),
			{ x1: 1000, y1: 1000, x2: 0, y2: 0 },
		);

	// finally convert back to x,y,width,height and add padding
	return { x: x1 - padding, y: y1 - padding, width: x2 - x1 + ( 2 * padding ), height: y2 - y1 + ( 2 * padding ) };
}
