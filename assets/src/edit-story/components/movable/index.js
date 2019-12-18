/**
 * External dependencies
 */
import Moveable from 'react-moveable';
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { forwardRef, useContext, createPortal } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Context from './context';

const Wrapper = styled.div`
  position: absolute;
  top: 0;
  left: 0;
  zIndex: ${ ( { zIndex } ) => `${ zIndex }` };
`;

function MovableWithRef( { zIndex, ...moveableProps }, ref ) {
	const { container, layer } = useContext( Context );
	if ( ! container || ! layer ) {
		return null;
	}
	const slot = (
		<Wrapper zIndex={ zIndex }>
			<Moveable
				ref={ ref }
				container={ container }
				{ ...moveableProps }
			/>
		</Wrapper>
	);
	return createPortal( slot, layer );
}

const Movable = forwardRef( MovableWithRef );

Movable.propTypes = {
	zIndex: PropTypes.number.isRequired,
};

export default Movable;
