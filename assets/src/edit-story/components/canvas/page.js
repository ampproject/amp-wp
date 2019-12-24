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
import SelectionMovable from '../selection';
import MovableLayer from '../movable/movableLayer';
import useCanvas from './useCanvas';
import Element from './element';

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
			<MovableLayer>
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
					<SelectionMovable
						selectedElement={ selectedElement }
						targetEl={ targetEl }
						pushEvent={ pushEvent }
					/>
				) }
			</MovableLayer>
		</Background>
	);
}

export default Page;
