/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useStory } from '../../app';
import { getPanels } from '../../panels';

const Background = styled.div`
	background-color: ${ ( { theme } ) => theme.colors.bg.v2 };
	padding: 16px 16px 0;
	height: 100%;
`;

const Wrapper = styled.div`
	background-color: ${ ( { theme } ) => theme.colors.fg.v1 };
	border: 1px solid ${ ( { theme } ) => theme.colors.fg.v2 };
	border-radius: 6px 6px 0 0;
	height: 100%;
	padding: 1em;
`;

function Inspector() {
	const {
		state: { selectedElements },
		actions: { setPropertiesOnSelectedElements },
	} = useStory();
	const panels = getPanels( selectedElements );
	const handleSetProperties = useCallback( ( properties ) => {
		// Filter out empty properties (only empty strings)
		const updatedKeys = Object.keys( properties )
			.filter( ( key ) => properties[ key ] !== '' );

		if ( updatedKeys.length === 0 ) {
			// Of course abort if no keys have a value
			return;
		}

		const actualProperties = updatedKeys
			.reduce( ( obj, key ) => ( { ...obj, [ key ]: properties[ key ] } ), {} );
		setPropertiesOnSelectedElements( actualProperties );
	}, [ setPropertiesOnSelectedElements ] );
	return (
		<Background>
			<Wrapper>
				{ panels.map( ( { Panel, type } ) => (
					<Panel key={ type } selectedElements={ selectedElements } onSetProperties={ handleSetProperties } />
				) ) }
			</Wrapper>
		</Background>
	);
}

export default Inspector;
