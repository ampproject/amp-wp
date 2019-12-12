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
	const {
		actions: { setBackgroundClickHandler },
	} = useCanvas();

	const {
		state: { currentPage, selectedElements },
		actions: { clearSelection, selectElementById, toggleElementIdInSelection },
	} = useStory();

	const [ targetEl, setTargetEl ] = useState( null );
	const [ pushEvent, setPushEvent ] = useState( null );

	useEffect( () => {
		setBackgroundClickHandler( () => clearSelection() );
	}, [ setBackgroundClickHandler, clearSelection ] );

	const handleSelectElement = useCallback( ( elId, evt ) => {
		if ( evt.metaKey ) {
			toggleElementIdInSelection( elId );
		} else {
			selectElementById( elId );
		}
		evt.stopPropagation();

		if ( 'pointerdown' === evt.type ) {
			evt.persist();
			setPushEvent( evt );
		}
	}, [ toggleElementIdInSelection, selectElementById ] );

	const singleSelection = 1 === selectedElements.length;

	return (
		<Background>
			{ currentPage && currentPage.elements.map( ( { type, id, ...rest } ) => {
				const comp = getComponentForType( type );
				const Comp = comp; // why u do dis, eslint?

				// @todo Improve this here, create some reasonable variables.
				const isSelected = selectedElements.filter( ( { id: selectedId } ) => id === selectedId ).length;
				// @todo Use the wrapper element around <Comp> as the target for Moveable instead.
				return (
					<Element
						key={ id }
						onClick={ ( evt ) => handleSelectElement( id, evt ) }
					>
						<Comp
							{ ...rest }
							onPointerDown={ ( evt ) => {
								if ( ! isSelected ) {
									handleSelectElement( id, evt );
								}
							} }
							forwardedRef={ isSelected ? setTargetEl : null }
							className={ isSelected && 1 < selectedElements.length ? 'target' : null }
						/>
					</Element>
				);
			} ) }
			{ singleSelection && targetEl && (
				<Movable
					targetEl={ targetEl }
					pushEvent={ pushEvent }
					selectedEl={ selectedElements[ 0 ] }
				/>
			) }
			{ 1 < selectedElements.length && (
				<Movable
					targets={ [].slice.call( document.querySelectorAll( '.target' ) ) } // @todo Array of references instead.
					targetEl={ null }
					selectedEl={ {} }
					pushEvent={ pushEvent }
				/>
			) }
		</Background>
	);
}

export default Page;
