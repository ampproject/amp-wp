/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useStory } from '../../app';
import useCanvas from './useCanvas';
import Movable from './../moveable';

const Background = styled.div.attrs( { className: 'container' } )`
	background-color: ${ ( { theme } ) => theme.colors.fg.v1 };
	position: relative;
	width: 100%;
	height: 100%;
`;

function Page() {
	const {
		actions: { setBackgroundClickHandler },
	} = useCanvas();

	const {
		state: { currentPage, selectedElements },
		actions: { clearSelection },
	} = useStory();

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
				return (
					<Movable
						key={ 'moveable-' + id }
						type={ type }
						x={ rest.x }
						y={ rest.y }
						rotationAngle={ rest.rotationAngle }
						selected={ 1 === selectedElements.length && selectedElements[ 0 ].id === id }
						rest={ rest }
						id={ id }
					/>
				);
			} ) }
		</Background>
	);
}

export default Page;
