/**
 * External dependencies
 */
import Modal from 'react-modal';
import PropTypes from 'prop-types';
import { createGlobalStyle } from 'styled-components';

/**
 * Internal dependencies
 */
import { ADMIN_TOOLBAR_HEIGHT } from '../../constants';

const PADDING_TOP = 70;
const PADDING_LEFT = 170;
const ADMIN_MENU_WIDTH = 160;
const ADMIN_MENU_FOLDED_WIDTH = 36;

export const GlobalStyle = createGlobalStyle`
	.WebStories_ReactModal__Content {
		position: absolute;
		top: ${ PADDING_TOP }px;
		right: ${ PADDING_LEFT }px;
		bottom: ${ PADDING_TOP }px;
		left: ${ PADDING_LEFT }px;
		overflow: auto;
		outline: none;
		background-color: ${ ( { theme } ) => theme.colors.bg.v1 };
	}

	.WebStories_ReactModal__Overlay {
		position: fixed;
		top: ${ ADMIN_TOOLBAR_HEIGHT }px;
		left: ${ ADMIN_MENU_WIDTH }px;
		right: 0;
		bottom: 0;
		background-color: ${ ( { theme } ) => theme.colors.bg.v1 };
		z-index: 10;
	}

	body.folded .WebStories_ReactModal__Overlay {
		left: ${ ADMIN_MENU_FOLDED_WIDTH }px;
	}
`;

function StyledModal( { children, ...props } ) {
	return (
		<Modal
			{ ...props }
			className="WebStories_ReactModal__Content"
			overlayClassName="WebStories_ReactModal__Overlay"
		>
			{ children }
		</Modal>
	);
}

StyledModal.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
};

export default StyledModal;
