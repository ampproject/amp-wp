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
	const [ targetRefs, setTargetRefs ] = useState( [] );

	const {
		state: { currentPage, selectedElements },
		actions: { clearSelection, selectElementById, toggleElementIdInSelection },
	} = useStory();

	const {
		state: { editingElement },
		actions: { setBackgroundClickHandler, setNodeForElement },
	} = useCanvas();

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

		// Persist the event so that a dragstart could be
		// later triggered without having to select the element first.
		if ( 'pointerdown' === evt.type ) {
			evt.persist();
			setPushEvent( evt );
		}
	}, [ toggleElementIdInSelection, selectElementById ] );

	const singleSelection = 1 === selectedElements.length;
	const hasSelection = 1 <= selectedElements.length;

	return (
		<Background>
			{ currentPage && currentPage.elements.map( ( { id, ...rest } ) => {
				const isSelected = Boolean( selectedElements.filter( ( { id: selectedId } ) => id === selectedId ).length );
				return (
					<Element
						key={ id }
						setNodeForElement={ setNodeForElement }
						setTargetRefs={ setTargetRefs }
						isEditing={ editingElement === id }
						element={ { id, ...rest } }
						isSelected={ isSelected }
						handleSelectElement={ handleSelectElement }
						forwardedRef={ singleSelection && isSelected ?
							setTargetEl : null
						}
					/>
				);
			} ) }
			{ hasSelection && ( targetEl || 1 < selectedElements.length ) && (
				<Movable
					targets={ targetRefs }
					targetEl={ targetEl }
					pushEvent={ pushEvent }
					selectedElement={ singleSelection ? selectedElements[ 0 ] : {} }
				/>
			) }
		</Background>
	);
}

export default Page;
