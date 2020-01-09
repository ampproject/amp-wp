/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Context from './context';

const Container = styled.div`
  position: absolute;
  inset: 0;
`;

const Layer = styled.div`
  position: absolute;
  top: 0;
  left: 0;
`;

// @todo: consider making this more generic tool: a set of tools layered on top
// of the editor, with the tool order controlled via zIndex.
function MovableLayer( { children } ) {
	const [ layer, setLayer ] = useState( null );
	const [ container, setContainer ] = useState( null );
	return (
		<Container ref={ setContainer }>
			<Context.Provider value={ { container, layer } }>
				{ children }

				<Layer ref={ setLayer } />
			</Context.Provider>
		</Container>
	);
}

MovableLayer.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
};

export default MovableLayer;
