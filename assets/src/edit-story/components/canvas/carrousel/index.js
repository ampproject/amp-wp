/**
 * External dependencies
 */
import styled from 'styled-components';
import { rgba } from 'polished';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { useStory } from '../../../app';
import { Navigable, NavigableGroup } from '../../focusable';

const List = styled( NavigableGroup ).attrs( {
	element: 'nav',
	role: 'tablist',
	tabindex: -1,
	direction: NavigableGroup.DIRECTION_HORIZONTAL,
	hotkey: 'meta+t',
} )`
	display: flex;
	flex-direction: row;
	align-items: flex-start;
	justify-content: center;
	height: 100%;
	padding-top: 1em;
`;

const Page = styled( Navigable ).attrs( {
	element: 'button',
	role: 'tab',
} )`
	background-color: ${ ( { theme, isActive } ) => rgba( theme.colors.fg.v1, isActive ? 1 : 0.1 ) };
	height: 90px;
	width: 51px;
	margin: 0 5px;
	cursor: pointer;
	border: 4px solid transparent;
	padding: 0;

	&:hover {
		background-color: ${ ( { theme } ) => theme.colors.fg.v1 };
	}

	&:focus, &:active {
		outline: none;
		border-color: ${ ( { theme } ) => theme.colors.action };
	}
`;

function CarouselPage( { index } ) {
	const {
		state: { currentPageIndex },
		actions: { setCurrentPageByIndex, deletePageByIndex },
	} = useStory();
	const deletePage = () => deletePageByIndex( index );
	const shortcuts = { backspace: deletePage, del: deletePage };
	return (
		<Page
			shortcuts={ shortcuts }
			onClick={ () => setCurrentPageByIndex( index ) }
			isActive={ index === currentPageIndex }
		/>
	);
}

function Carousel() {
	const {
		state: { pages },
	} = useStory();
	return (
		<List>
			{ pages.map( ( page, index ) => (
				<CarouselPage key={ index } index={ index } />
			) ) }
		</List>
	);
}

export default Carousel;

CarouselPage.propTypes = {
	index: PropTypes.number.isRequired,
};
