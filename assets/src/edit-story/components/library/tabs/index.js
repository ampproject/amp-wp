/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import MediaIcon from './media.svg';
import TextIcon from './text.svg';
import ShapesIcon from './shapes.svg';
import LinksIcon from './links.svg';

const Tabs = styled.ul`
	background: ${ ( { theme } ) => theme.colors.bg.v3 };
	display: flex;
	height: 100%;
	margin: 0;
`;

const Tab = styled.li`
	width: 64px;
	height: 100%;
	color: ${ ( { theme } ) => theme.colors.fg.v1 };
`;

const Icon = styled.a`
	color: inherit;
	background: ${ ( { isActive, theme } ) => isActive ? theme.colors.bg.v4 : 'transparent' };
	height: 100%;
	display: flex;
	justify-content: center;
	align-items: center;

	&:hover {
		color: inherit;
	}

	${ ( { isActive } ) => ! isActive && `
		opacity: .4;
		&:hover { opacity: 1; }
	` }

	svg {
		width: 22px;
		height: 22px;
	}
`;

function Media( props ) {
	return (
		<Tab>
			<Icon { ...props }>
				<MediaIcon />
			</Icon>
		</Tab>
	);
}

function Text( props ) {
	return (
		<Tab>
			<Icon { ...props }>
				<TextIcon />
			</Icon>
		</Tab>
	);
}

function Shapes( props ) {
	return (
		<Tab>
			<Icon { ...props }>
				<ShapesIcon />
			</Icon>
		</Tab>
	);
}

function Links( props ) {
	return (
		<Tab>
			<Icon { ...props }>
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
