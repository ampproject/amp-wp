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
import { ElementWithPosition, ElementWithSize, ElementWithRotation } from '../../elements/shared';
import { useUnits } from '../../units';
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

function FrameElement( { element } ) {
	const { id, type } = element;
	const { Frame } = getDefinitionForType( type );
	const elementRef = useRef();

	const { actions: { setNodeForElement, handleSelectElement } } = useCanvas();
	const { state: { selectedElements } } = useStory();
	const { actions: { getBox } } = useUnits();

	useLayoutEffect( () => {
		setNodeForElement( id, elementRef.current );
	}, [ id, setNodeForElement ] );

	const isSelected = selectedElements.includes( id );

	// eslint-disable-next-line @wordpress/no-unused-vars-before-return
	const box = getBox( element );

	return (
		<Wrapper
			ref={ elementRef }
			{ ...box }
			onMouseDown={ ( evt ) => {
				if ( ! isSelected ) {
					handleSelectElement( id, evt );
				}
				evt.stopPropagation();
			} }
		>
			{ Frame && (
				<Frame element={ element } box={ box } />
			) }
		</Wrapper>
	);
}

FrameElement.propTypes = {
	element: PropTypes.object.isRequired,
};

export default FrameElement;
