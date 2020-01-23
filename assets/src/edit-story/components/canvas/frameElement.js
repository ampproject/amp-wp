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
  pointer-events: initial;

  &:focus, &:active, &:hover {
    outline: 1px solid ${ ( { theme } ) => theme.colors.selection };
  }
`;

function FrameElement( {
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
	const { Frame } = getDefinitionForType( type );
	const element = useRef();

	const {
		actions: { setNodeForElement, handleSelectElement },
	} = useCanvas();

	const {
		state: { selectedElements },
	} = useStory();

	useLayoutEffect( () => {
		setNodeForElement( id, element.current );
	}, [ id, setNodeForElement ] );

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
			{ Frame && (
				<Frame { ...props } />
			) }
		</Wrapper>
	);
}

FrameElement.propTypes = {
	element: PropTypes.object.isRequired,
};

export default FrameElement;
