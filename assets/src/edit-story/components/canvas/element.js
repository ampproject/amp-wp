/**
 * External dependencies
 */
import styled from 'styled-components';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useLayoutEffect, useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { getDefinitionForType } from '../../elements';
import { useStory } from '../../app';
import { ElementWithPosition, ElementWithSize, ElementWithRotation, getBox } from '../../elements/shared';
import useCanvas from './useCanvas';

const Wrapper = styled.div`
	${ ElementWithPosition }
	${ ElementWithSize }
	${ ElementWithRotation }
`;

function Element( {
	element: {
		id,
		type,
		x,
		y,
		width,
		height,
		rotationAngle,
		isFullbleed,
		...rest
	},
} ) {
	const { Display, Edit } = getDefinitionForType( type );
	const element = useRef();

	const {
		state: { editingElement },
		actions: { setNodeForElement, handleSelectElement },
	} = useCanvas();

	const {
		state: { selectedElements },
	} = useStory();

	useLayoutEffect( () => {
		setNodeForElement( id, element.current );
	}, [ id, setNodeForElement ] );

	const isEditing = ( editingElement === id );
	const isSelected = selectedElements.includes( id );

	const box = getBox( { x, y, width, height, rotationAngle, isFullbleed } );
	const props = { ...box, ...rest, id };

	return (
		<Wrapper
			ref={ element }
			{ ...box }
			onMouseDown={ ( evt ) => {
				if ( ! isSelected ) {
					handleSelectElement( id, evt );
				}
				evt.stopPropagation();
			} }
		>
			{ isEditing ?
				( <Edit { ...props } /> ) :
				( <Display { ...props } /> ) }
		</Wrapper>
	);
}

Element.propTypes = {
	element: PropTypes.object.isRequired,
};

export default Element;
