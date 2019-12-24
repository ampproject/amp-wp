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

const DEFAULT_Z_INDEX = 10;

const Wrapper = styled.div`
  position: absolute;
  top: 0;
  left: 0;
  z-index: ${ ( { zIndex } ) => `${ zIndex }` };
`;

function MovableWithRef( { zIndex, ...moveableProps }, ref ) {
	const { container, layer } = useContext( Context );
	if ( ! container || ! layer ) {
		return null;
	}
	const slot = (
		<Wrapper
			zIndex={ zIndex || DEFAULT_Z_INDEX }
			onMouseDown={ ( evt ) => evt.stopPropagation() }>
			<Moveable
				ref={ ref }
				container={ container }
				{ ...moveableProps }
			/>
		</Wrapper>
	);
	return createPortal( slot, layer );
}

MovableWithRef.propTypes = {
	zIndex: PropTypes.number,
};

MovableWithRef.defaultProps = {
	zIndex: DEFAULT_Z_INDEX,
};

const Movable = forwardRef( MovableWithRef );

export default Movable;
