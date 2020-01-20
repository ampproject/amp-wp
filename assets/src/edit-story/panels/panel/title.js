/**
 * External dependencies
 */
import styled from 'styled-components';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import panelContext from './context';
import Arrow from './arrow.svg';

const Header = styled.header`
	background-color: rgba( 0, 0, 0, .07 );
	color: ${ ( { theme } ) => theme.colors.bg.v2 };
	display: flex;
	padding: 10px 20px;
	justify-content: space-between;
	align-items: center;
`;

const H = styled.h1`
	color: inherit;
	margin: 0;
	font-weight: 500;
	font-size: 16px;
	line-height: 19px;
`;

const Collapse = styled.button.attrs( { type: 'button' } )`
	color: inherit;
	width: 28px;
	height: 28px;
	border: 0;
	padding: 0;
	background: transparent;
	display: flex; // removes implicit line-height padding from child element

	svg {
		width: 28px;
		height: 28px;
		${ ( { isCollapsed } ) => ! isCollapsed && `transform: rotate(.5turn);` }
	}
`;

function Title( { children } ) {
	const {
		state: { isCollapsed },
		actions: { collapse, expand },
	} = useContext( panelContext );

	return (
		<Header>
			<H>
				{ children }
			</H>
			<Collapse isCollapsed={ isCollapsed } onClick={ isCollapsed ? expand : collapse }>
				<Arrow />
			</Collapse>
		</Header>
	);
}

Title.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
};

export default Title;
