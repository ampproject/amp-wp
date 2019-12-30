/**
 * External dependencies
 */
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
  width: 0;
  height: 0;
  z-index: ${ ( { zIndex } ) => `${ zIndex }` };
`;

function InOverlayWithRef( { zIndex, render, children }, ref ) {
	const { container, overlay } = useContext( Context );
	if ( ! container || ! overlay ) {
		return null;
	}
	const slot = (
		<Wrapper
			ref={ ref }
			zIndex={ zIndex || 0 }
			onMouseDown={ ( evt ) => evt.stopPropagation() }>
			{ render ? render( { container, overlay } ) : children }
		</Wrapper>
	);
	return createPortal( slot, overlay );
}

InOverlayWithRef.propTypes = {
	zIndex: PropTypes.number,
};

InOverlayWithRef.defaultProps = {
	zIndex: 0,
};

const InOverlay = forwardRef( InOverlayWithRef );

export default InOverlay;
