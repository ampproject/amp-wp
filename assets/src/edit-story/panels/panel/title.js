/**
 * External dependencies
 */
import styled from 'styled-components';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useContext, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import useInspector from '../../components/inspector/useInspector';
import panelContext from './context';
import DragHandle from './handle';
import Arrow from './arrow.svg';

const Header = styled.h2`
	background-color: ${ ( { theme, isPrimary } ) => isPrimary ? theme.colors.fg.v2 : theme.colors.fg.v1 };
	border: 0px solid ${ ( { theme } ) => theme.colors.fg.v2 };
	border-top-width: ${ ( { isPrimary } ) => isPrimary ? 0 : '1px' };
	color: ${ ( { theme } ) => theme.colors.bg.v2 };
	padding: 10px 20px;
	${ ( { hasResizeHandle } ) => hasResizeHandle && 'padding-top: 0;' };
	margin: 0;
	position: relative;
	display: flex;
	flex-direction: column;
	justify-content: flex-start;
	align-items: stretch;
`;

const HeaderButton = styled.button.attrs( { type: 'button' } )`
	color: inherit;
	border: 0;
	padding: 0;
	background: transparent;
	display: flex;
	justify-content: space-between;
	align-items: center;
	cursor: pointer;
`;

const Heading = styled.span`
	color: inherit;
	margin: 0;
	font-weight: 500;
	font-size: 16px;
	line-height: 19px;
`;

const Collapse = styled.span`
	color: inherit;
	width: 28px;
	height: 28px;
	display: flex; // removes implicit line-height padding from child element

	svg {
		width: 28px;
		height: 28px;
		${ ( { isCollapsed } ) => isCollapsed && `transform: rotate(.5turn);` }
	}
`;

function Title( { children, isPrimary, isResizable } ) {
	const {
		state: { isCollapsed, height, panelContentId },
		actions: { collapse, expand, setHeight },
	} = useContext( panelContext );
	const { state: { inspectorContentHeight } } = useInspector();

	// Max panel height is set to 70% of full available height.
	const maxHeight = Math.round( inspectorContentHeight * 0.7 );

	const handleHeightChange = useCallback(
		( deltaHeight ) => setHeight( ( value ) => Math.max( 0, Math.min( maxHeight, value + deltaHeight ) ) ),
		[ setHeight, maxHeight ],
	);

	return (
		<Header isPrimary={ isPrimary } hasResizeHandle={ isResizable && ! isCollapsed }>
			{ isResizable && ! isCollapsed && (
				<DragHandle
					height={ height }
					minHeight={ 0 }
					maxHeight={ maxHeight }
					handleHeightChange={ handleHeightChange }
				/>
			) }
			<HeaderButton
				onClick={ isCollapsed ? expand : collapse }
				aria-label={ __( 'Collapse/expand panel', 'amp' ) }
				aria-expanded={ ! isCollapsed }
				aria-controls={ panelContentId }
			>
				<Heading>
					{ children }
				</Heading>
				<Collapse isCollapsed={ isCollapsed }>
					<Arrow />
				</Collapse>
			</HeaderButton>
		</Header>
	);
}

Title.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
	isPrimary: PropTypes.bool,
	isResizable: PropTypes.bool,
};

Title.defaultProps = {
	isPrimary: false,
	isResizable: false,
};

export default Title;
