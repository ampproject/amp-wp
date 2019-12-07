/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { Navigable, NavigableGroup } from '../../focusable';
import MediaIcon from './media.svg';
import TextIcon from './text.svg';
import ShapesIcon from './shapes.svg';
import LinksIcon from './links.svg';

const Tabs = styled( NavigableGroup ).attrs( {
	element: 'nav',
	role: 'tablist',
	tabindex: -1,
	direction: NavigableGroup.DIRECTION_HORIZONTAL,
	hotkey: 'meta+t',
} )`
	background: ${ ( { theme } ) => theme.colors.bg.v3 };
	display: flex;
	height: 100%;
	margin: 0;
`;

const Tab = styled( Navigable ).attrs( {
	element: 'button',
	role: 'tab',
} )`
	width: 64px;
	height: 100%;
	border: 0;
	background: transparent;
	padding: 0;
	cursor: pointer;
	color: ${ ( { theme, isActive } ) => isActive ? theme.colors.fg.v1 : theme.colors.fg.v4 };
	background: ${ ( { theme, isActive } ) => isActive ? theme.colors.bg.v4 : 'transparent' };

	&:focus, &:active {
		outline: none;
	}

	&:hover {
		color: ${ ( { theme } ) => theme.colors.fg.v1 };
	}
`;

const Icon = styled.span`
	color: inherit;
	height: 100%;
	display: flex;
	justify-content: center;
	align-items: center;

	svg {
		width: 22px;
		height: 22px;

		${ Tab }:focus &,
		${ Tab }:active & {
			outline: 5px auto #212121;
  		outline: 5px auto -webkit-focus-ring-color;
		}
	}
`;

function Media( props ) {
	return (
		<Tab { ...props }>
			<Icon>
				<MediaIcon />
			</Icon>
		</Tab>
	);
}

function Text( props ) {
	return (
		<Tab { ...props }>
			<Icon>
				<TextIcon />
			</Icon>
		</Tab>
	);
}

function Shapes( props ) {
	return (
		<Tab { ...props }>
			<Icon>
				<ShapesIcon />
			</Icon>
		</Tab>
	);
}

function Links( props ) {
	return (
		<Tab { ...props }>
			<Icon>
				<LinksIcon />
			</Icon>
		</Tab>
	);
}

export {
	Tabs,
	Media,
	Text,
	Shapes,
	Links,
};
