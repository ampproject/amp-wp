/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useCallback, useEffect, useState, useRef } from '@wordpress/element';

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

	const targetRefs = useRef( [] );
	useEffect( () => {
		// Update the size of targetRefs when the selected elements changes.
		targetRefs.current = targetRefs.current.slice( 0, selectedElements.length );
	}, [ selectedElements ] );

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
			{ currentPage && currentPage.elements.map( ( { type, id, ...rest }, i ) => {
				const comp = getComponentForType( type );
				const Comp = comp; // why u do dis, eslint?

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
								// Ignore this event if multi-selection is being done.
								if ( ! isSelected && ! evt.metaKey ) {
									handleSelectElement( id, evt );
								}
							} }
							forwardedRef={ ! isSelected ? null : ( el ) => {
								// @todo This doesn't really work as expected, we should probably create a ref for targetEl.
								if ( singleSelection ) {
									setTargetEl( el );
									return;
								}
								targetRefs.current[ i ] = {
									id,
									...rest,
									ref: el,
								};
							} }
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
			{ 1 < targetRefs.current.length && (
				<Movable
					targets={ targetRefs.current }
					targetEl={ null }
					selectedEl={ {} }
					pushEvent={ pushEvent }
				/>
			) }
		</Background>
	);
}

export default Page;
