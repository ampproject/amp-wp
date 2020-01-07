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
		state: { backgroundMouseDownHandler, editingElement },
		actions: { setBackgroundMouseDownHandler, setNodeForElement, clearEditing },
	} = useCanvas();

	const [ pushEvent, setPushEvent ] = useState( null );

	useEffect( () => {
		setBackgroundMouseDownHandler( () => clearSelection() );
	}, [ setBackgroundMouseDownHandler, clearSelection ] );

	const handleSelectElement = useCallback( ( elId, evt ) => {
		if ( editingElement && elId !== editingElement ) {
			clearEditing();
		}
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
	}, [ editingElement, clearEditing, toggleElementIdInSelection, selectElementById ] );

	const selectedElement = selectedElements.length === 1 ? selectedElements[ 0 ] : null;

	return (
		<Background onMouseDown={ backgroundMouseDownHandler }>
			{ currentPage && currentPage.elements.map( ( { id, ...rest } ) => {
				const isSelected = Boolean( selectedElement && selectedElement.id === id );

				return (
					<Element
						key={ id }
						setNodeForElement={ setNodeForElement }
						isEditing={ editingElement === id }
						element={ { id, ...rest } }
						isSelected={ isSelected }
						handleSelectElement={ handleSelectElement }
						forwardedRef={ isSelected ? setTargetEl : null }
					/>
				);
			} ) }

			{ selectedElement && targetEl && (
				<Movable
					selectedElement={ selectedElement }
					targetEl={ targetEl }
					pushEvent={ pushEvent }
				/>
			) }
		</Background>
	);
}

export default Page;
