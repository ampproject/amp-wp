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
import { getComponentForType } from '../../elements';
import useCanvas from './useCanvas';
import Movable from './../movable';

const Background = styled.div.attrs( { className: 'container' } )`
	background-color: ${ ( { theme } ) => theme.colors.fg.v1 };
	position: relative;
	width: 100%;
	height: 100%;
`;

const Element = styled.div`
	cursor: pointer;
	user-select: none;
`;

function Page() {
	const [ targetEl, setTargetEl ] = useState( null );

	const {
		state: { currentPage, selectedElements },
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

	useEffect( () => {
		setBackgroundClickHandler( () => clearSelection() );
	}, [ setBackgroundClickHandler, clearSelection ] );

	return (
		<Background>
			{ currentPage && currentPage.elements.map( ( { type, id, ...rest } ) => {
				const comp = getComponentForType( type );
				const Comp = comp; // why u do dis, eslint?

				// @todo Improve this here, create some reasonable variables.
				const isSelected = selectedElements.filter( ( { id: selectedId } ) => id === selectedId ).length;
				return (
					<Element key={ id } onClick={ handleSelectElement( id ) }>
						<Comp
							{ ...rest }
							forwardedRef={ isSelected && 1 === selectedElements.length ? setTargetEl : null }
							className={ isSelected && 1 < selectedElements.length ? 'target' : null }
						/>
					</Element>
				);
			} ) }
			{ targetEl && 1 === selectedElements.length && (
				<Movable
					targetEl={ targetEl }
					selectedEl={ selectedElements[ 0 ] }
				/>
			) }
			{ 1 < selectedElements.length && (
				<Movable
					targets={ [].slice.call( document.querySelectorAll( '.target' ) ) } // @todo Array of references instead.
					targetEl={ null }
					selectedEl={ {} }
				/>
			) }
		</Background>
	);
}

export default Page;
