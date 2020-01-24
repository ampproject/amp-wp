/**
 * External dependencies
 */
import styled from 'styled-components';
import PropTypes from 'prop-types';
import uuid from 'uuid/v4';

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

function Panel( { initialHeight, name, children } ) {
	const [ isCollapsed, setIsCollapsed ] = useState( false );
	const [ height, setHeight ] = useState( initialHeight );

	const collapse = useCallback( () => setIsCollapsed( true ), [] );
	const expand = useCallback( () => setIsCollapsed( false ), [] );

	const panelContentId = `panel-${ name }-${ uuid() }`;

	const contextValue = {
		state: {
			height,
			isCollapsed,
			panelContentId,
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
	name: PropTypes.string.isRequired,
	initialHeight: PropTypes.number,
};

Panel.defaultProps = {
	initialHeight: null,
};

export default Panel;
