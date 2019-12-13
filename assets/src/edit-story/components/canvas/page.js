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

				// Ignore multi-selection for now.
				const isSelected = selectedElements.length ? selectedElements[ 0 ].id === id : false;
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
						/>
					</Element>
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
