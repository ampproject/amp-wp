/**
 * External dependencies
 */
import styled from 'styled-components';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useState, useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import panelContext from './context';

const Wrapper = styled.section`
	display: flex;
	flex-direction: column;
`;

function Panel( { initialHeight, children } ) {
	const [ isCollapsed, setIsCollapsed ] = useState( false );
	const [ height, setHeight ] = useState( initialHeight );

	const collapse = useCallback( () => setIsCollapsed( true ), [] );
	const expand = useCallback( () => setIsCollapsed( false ), [] );

	const contextValue = {
		state: {
			height,
			isCollapsed,
		},
		actions: {
			setHeight,
			collapse,
			expand,
		},
	};

	const ContextProvider = panelContext.Provider;

	return (
		<Wrapper>
			<ContextProvider value={ contextValue }>
				{ children }
			</ContextProvider>
		</Wrapper>
	);
}

Panel.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
	initialHeight: PropTypes.number,
};

Panel.defaultProps = {
	initialHeight: null,
};

export default Panel;
