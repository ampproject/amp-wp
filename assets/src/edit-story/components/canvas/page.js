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
import useCanvas from './useCanvas';
import Element from './element';
import Movable from './../movable';

const Background = styled.div.attrs( { className: 'container' } )`
	background-color: ${ ( { theme } ) => theme.colors.fg.v1 };
	position: relative;
	width: 100%;
	height: 100%;
`;

function Page() {
	const [ targetEl, setTargetEl ] = useState( null );

	const {
		state: { currentPage, selectedElements },
		actions: { clearSelection, selectElementById, toggleElementIdInSelection },
	} = useStory();

	const {
		state: { editingElement },
		actions: { setBackgroundClickHandler, setNodeForElement },
	} = useCanvas();

	const [ pushEvent, setPushEvent ] = useState( null );

	const targetRefs = useRef( [] );

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

		// Persist the event so that a dragstart could be
		// later triggered without having to select the element first.
		if ( 'pointerdown' === evt.type ) {
			evt.persist();
			setPushEvent( evt );
		}
	}, [ toggleElementIdInSelection, selectElementById ] );

	const singleSelection = 1 === selectedElements.length;
	const hasSelection = 1 <= selectedElements.length;

	useEffect( () => {
		console.log( selectedElements.length );
	}, [ selectedElements ] );

	return (
		<Background>
			{ currentPage && currentPage.elements.map( ( { id, ...rest }, i ) => {
				const isSelected = Boolean( selectedElements.filter( ( { id: selectedId } ) => id === selectedId ).length );

				return (
					<Element
						key={ id }
						setNodeForElement={ setNodeForElement }
						isEditing={ editingElement === id }
						element={ { id, ...rest } }
						isSelected={ isSelected }
						handleSelectElement={ handleSelectElement }
						forwardedRef={ singleSelection && isSelected ?
							setTargetEl :
							( el ) => {
								// @TODO We should also remove the nodes that don't exist anymore!
								if ( ! isSelected ) {
									return;
								}
								// Add the element to the list of refs.
								targetRefs.current[ i ] = {
									id,
									...rest,
									ref: el,
								};
							}
						}
					/>
				);
			} ) }
			{ hasSelection && ( targetEl || Boolean( targetRefs.current.length ) ) && (
				<Movable
					targets={ targetRefs.current }
					targetEl={ targetEl }
					pushEvent={ pushEvent }
					selectedEl={ singleSelection ? selectedElements[ 0 ] : {} }
				/>
			) }
		</Background>
	);
}

export default Page;
