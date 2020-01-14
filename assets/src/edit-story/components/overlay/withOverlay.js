/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useState, forwardRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import useCombinedRefs from '../../utils/useCombinedRefs';
import Context from './context';

const Overlay = styled.div`
  position: absolute;
  top: 0;
  left: 0;
  width: 0;
  height: 0;
  z-index: 1;
`;

function withOverlay( Comp ) {
	return forwardRef( ( { children, ...rest }, ref ) => {
		const [ overlay, setOverlay ] = useState( null );
		const [ container, setContainer ] = useState( null );
		return (
			<Context.Provider value={ { container, overlay } }>
				<Comp ref={ useCombinedRefs( ref, setContainer ) } { ...rest }>
					{ children }
					<Overlay ref={ setOverlay } />
				</Comp>
			</Context.Provider>
		);
	} );
}

export default withOverlay;
