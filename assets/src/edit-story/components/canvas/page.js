/**
 * External dependencies
 */
import styled from 'styled-components';

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

const useDoubleClick = ( onClick, onDoubleClick ) => {
	const [ target, setTarget ] = useState( null );
	const [ lastEvent, setLastEvent ] = useState( null );
	const getHandler = ( newTarget ) => ( evt ) => {
		evt.stopPropagation();

		if ( target !== newTarget ) {
			if ( target ) {
				onClick( target, evt );
			}
			setTarget( newTarget );
			evt.persist();
			setLastEvent( evt );
			return;
		}

		onDoubleClick( target, evt );
		setTarget( null );
	};
	useEffect( () => {
		if ( ! target ) {
			return undefined;
		}
		const int = setTimeout( () => {
			setTarget( null );
			onClick( target, lastEvent );
		}, 200 );
		return () => {
			clearTimeout( int );
		};
	}, [ target, lastEvent, onClick ] );

	return getHandler;
};

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
	user-select: none;
	${ ( { isPassive } ) => isPassive ? 'opacity: .4;' : 'cursor: pointer;' }
`;

function Page() {
	const {
		state: { currentPage, hasSelection, selectedElements },
		actions: { clearSelection, selectElementById, toggleElementIdInSelection },
	} = useStory();
	const {
		state: { isEditing, editingElement },
		actions: { setBackgroundClickHandler, setEditingElement },
	} = useCanvas();
	const handleSelectElement = useCallback( ( id, evt ) => {
		if ( evt.metaKey ) {
			toggleElementIdInSelection( id );
		} else {
			selectElementById( id );
		}
		evt.stopPropagation();
	}, [ toggleElementIdInSelection, selectElementById ] );
	const handleEnterEditMode = ( id ) => {
		// Only edited element is selected.
		selectElementById( id );
		setEditingElement( id );
	};
	const getClickHandler = useDoubleClick( handleSelectElement, handleEnterEditMode );
	const selectionProps = hasSelection ? getUnionSelection( selectedElements ) : {};
	useEffect( () => {
		setBackgroundClickHandler( () => clearSelection() );
	}, [ setBackgroundClickHandler, clearSelection ] );
	return (
		<Background>
			{ currentPage && currentPage.elements.map( ( { type, id, ...rest } ) => {
				const { hasEditMode, Display, Edit } = getDefinitionForType( type );

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

				// Otherwise display this as non-passive Display.
				const onClick = hasEditMode ? getClickHandler( id ) : ( evt ) => handleSelectElement( id, evt );
				return (
					<Element key={ id } onClick={ onClick }>
						<Display { ...rest } />
					</Element>
				);
			} ) }
			{ hasSelection && ! isEditing && (
				<Selection { ...selectionProps } />
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
			( sum, el ) => ( {
				x1: Math.min( el.x1, sum.x1 ),
				y1: Math.min( el.y1, sum.y1 ),
				x2: Math.max( el.x2, sum.x2 ),
				y2: Math.max( el.y2, sum.y2 ),
			} ),
			{ x1: Number.MAX_VALUE, y1: Number.MAX_VALUE, x2: 0, y2: 0 },
		);

	// finally convert back to x,y,width,height and add padding
	return { x: x1 - padding, y: y1 - padding, width: x2 - x1 + ( 2 * padding ), height: y2 - y1 + ( 2 * padding ) };
}
