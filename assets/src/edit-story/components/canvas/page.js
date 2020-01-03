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
import { DesignMode, useStory } from '../../app';
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
		state: { designMode, currentPage, selectedElements },
		actions: { setDesignMode, clearSelection, selectElementById, toggleElementIdInSelection },
	} = useStory();

	const {
		state: { editingElement },
		actions: { setBackgroundClickHandler, setNodeForElement },
	} = useCanvas();

	const [ pushEvent, setPushEvent ] = useState( null );

	useEffect( () => {
		setBackgroundClickHandler( () => {
			if ( designMode === DesignMode.REPLAY ) {
				setDesignMode( DesignMode.DESIGN );
				return;
			}
			clearSelection();
		} );
	}, [ designMode, setBackgroundClickHandler, clearSelection, setDesignMode ] );

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

	const selectedElement = selectedElements.length === 1 ? selectedElements[ 0 ] : null;

	return (
		<Background>
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

			{ designMode === DesignMode.DESIGN && selectedElement && targetEl && (
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
