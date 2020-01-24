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

const Form = styled.form`
	margin: 10px 20px;
	overflow: auto;

	${ ( { hidden } ) => hidden && 'display: none' }
`;

function Content( { children, ...rest } ) {
	const { state: { isCollapsed, height, panelContentId } } = useContext( panelContext );

	const formStyle = {
		height: height === null ? 'auto' : `${ height }px`,
	};

	return (
		<Form style={ formStyle } { ...rest } id={ panelContentId } hidden={ isCollapsed }>
			{ children }
		</Form>
	);
}

Content.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
};

export default Content;
