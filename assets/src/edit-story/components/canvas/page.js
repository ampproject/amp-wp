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
	const moveable = useRef();

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

		if ( 'pointerdown' === evt.type ) {
			evt.persist();
			setPushEvent( evt );
		}
	}, [ toggleElementIdInSelection, selectElementById ] );

	const singleSelection = 1 === selectedElements.length;

	// Whenever selection change, update moveable rect
	useEffect( () => {
		if ( moveable.current ) {
			moveable.current.updateRect();
		}
	}, [ selectedElements ] );

	return (
		<Background>
			{ currentPage && currentPage.elements.map( ( { id, ...rest } ) => {
				const isSelected = selectedElements.length === 1 && selectedElements.contains( id );

				return (
					<Element
						key={ id }
						setNodeForElement={ setNodeForElement }
						handleSelectElement={ handleSelectElement }
						isEditing={ editingElement === id }
						element={ { id, ...rest } }
						onPointerDown={ ( evt ) => {
							if ( ! isSelected ) {
								handleSelectElement( id, evt );
							}
						} }
						forwardedRef={ isSelected ? setTargetEl : null }
					/>
				);
			} ) }

			{ singleSelection && targetEl && (
				<Movable
					rotationAngle={ selectedElements[ 0 ].rotationAngle }
					targetEl={ targetEl }
					pushEvent={ pushEvent }
					type={ selectedElements[ 0 ].type }
					x={ selectedElements[ 0 ].x }
					y={ selectedElements[ 0 ].y }
				/>
			) }
		</Background>
	);
}

export default Page;
