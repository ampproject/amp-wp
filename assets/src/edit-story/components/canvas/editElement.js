/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { getDefinitionForType } from '../../elements';
import { ElementWithPosition, ElementWithSize, ElementWithRotation } from '../../elements/shared';
import { useUnits } from '../../units';

// Background color is used to make the edited element more prominent and
// easier to see.
const Wrapper = styled.div`
  ${ ElementWithPosition }
  ${ ElementWithSize }
  ${ ElementWithRotation }
  pointer-events: initial;
	background-color: ${ ( { theme } ) => theme.colors.whiteout };
`;

function EditElement( { element } ) {
	const { type } = element;
	const { actions: { getBox } } = useUnits();

	// eslint-disable-next-line @wordpress/no-unused-vars-before-return
	const { Edit } = getDefinitionForType( type );

	// eslint-disable-next-line @wordpress/no-unused-vars-before-return
	const box = getBox( element );

	return (
		<Wrapper
			{ ...box }
			onMouseDown={ ( evt ) => evt.stopPropagation() }
		>
			<Edit element={ element } box={ box } />
		</Wrapper>
	);
}

EditElement.propTypes = {
	element: PropTypes.object.isRequired,
};

export default EditElement;
