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

const Wrapper = styled.div`
	min-height: 100%;
`;

function DesignInspector() {
	const {
		state: { selectedElements },
		actions: { deleteSelectedElements, updateSelectedElements },
	} = useStory();
	const panels = getPanels( selectedElements );

	const handleSetProperties = useCallback( ( properties ) => {
		// Filter out empty properties (empty strings specifically)
		const updatedKeys = Object.keys( properties )
			.filter( ( key ) => properties[ key ] !== '' );

		if ( updatedKeys.length === 0 ) {
			// Of course abort if no keys have a value
			return;
		}

		const actualProperties = updatedKeys
			.reduce( ( obj, key ) => ( { ...obj, [ key ]: properties[ key ] } ), {} );
		updateSelectedElements( { properties: actualProperties } );
	}, [ updateSelectedElements ] );
	return (

		<Wrapper>
			{ panels.map( ( { Panel, type } ) => (
				<Panel key={ type } deleteSelectedElements={ deleteSelectedElements } selectedElements={ selectedElements } onSetProperties={ handleSetProperties } />
			) ) }
		</Wrapper>
	);
}

export default DesignInspector;
