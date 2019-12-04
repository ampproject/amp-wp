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
import Movable from './../moveable';

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
	const handleSelectElement = useCallback( ( id, evt ) => {
		if ( evt.metaKey ) {
			toggleElementIdInSelection( id );
		} else {
			selectElementById( id );
		}
		evt.stopPropagation();
	}, [ toggleElementIdInSelection, selectElementById ] );

	useEffect( () => {
		setBackgroundClickHandler( ( e ) => {
			// @todo For some reason, the propagation stop above doesn't seem to be working.
			if ( 3 !== e.eventPhase ) {
				clearSelection();
			}
		} );
	}, [ setBackgroundClickHandler, clearSelection ] );

	return (
		<Background>
			{ currentPage && currentPage.elements.map( ( { type, id, ...rest } ) => {
				const comp = getComponentForType( type );
				const Comp = comp; // why u do dis, eslint?
				return (
					<>
						<Element ref={ setTargetEl } key={ id } onClick={ ( evt ) => handleSelectElement( id, evt ) }>
							<Comp { ...rest } />
						</Element>
						{ targetEl && (
							<Movable
								type={ type }
								x={ rest.x }
								y={ rest.y }
								rotationAngle={ rest.rotationAngle }
								targetEl={ targetEl }
								selected={ 1 === selectedElements.length && selectedElements[ 0 ].id === id }
							/>
						) }
					</>
				);
			} ) }
		</Background>
	);
}

export default Page;
