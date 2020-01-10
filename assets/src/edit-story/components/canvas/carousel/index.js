/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useEffect, useRef, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import useAdminMenuCollapsed from '../../../utils/useAdminMenuCollapsed';
import useWindowSize from '../../../utils/useWindowSize';
import { useStory } from '../../../app';
import AddPage from '../addpage';
import LeftArrowIcon from './leftArrow.svg';
import RightArrowIcon from './rightArrow.svg';

const PAGE_WIDTH = 56;
const PAGE_HEIGHT = 100;

const Wrapper = styled.div`
	position: relative;
	display: grid;
	grid-template-columns: 40px 1fr 40px ${ PAGE_WIDTH }px;
	grid: ${ ( { hasHorizontalOverflow } ) => hasHorizontalOverflow ?
		`".   carousel   .   action" auto / 20px 1fr 20px ${ PAGE_WIDTH }px` :
		'"carousel   action" auto / auto auto' };
	${ ( { hasHorizontalOverflow } ) => ! hasHorizontalOverflow && `
		column-gap: 5px;
	` }
	height: 100%;
	width: 100%;
	background-color: ${ ( { theme } ) => theme.colors.bg.v6 };
	color:  ${ ( { theme } ) => theme.colors.fg.v1 };
`;

const Area = styled.div`
	grid-area: ${ ( { area } ) => area };
	height: 100%;
	width: 100%;
`;

const List = styled( Area )`
	display: flex;
	${ ( { hasHorizontalOverflow } ) => ! hasHorizontalOverflow && `
		justify-content: flex-end;
	` }
	height: 100%;
	padding: 1em 0;
	overflow-x: ${ ( { hasHorizontalOverflow } ) => hasHorizontalOverflow ? 'scroll' : 'visible' };
`;

const ActionArea = styled( Area )`
	justify-content: flex-start;
`;

const NavigationButton = styled.div`
	width: ${ PAGE_WIDTH }px;
	height: 100%;
	display: flex;
	align-items: center;
	justify-content: center;
	cursor: pointer;
	position: absolute;

	svg {
		width: .5em;
		height: 1em;
	}
`;

const LeftNavigationButton = styled( NavigationButton )`
	background: linear-gradient(to left, rgba(29, 34, 47, 0) 0%, ${ ( { theme } ) => theme.colors.bg.v6 } 52%);
	left: 0;
	justify-content: flex-start;
	padding-left: 10px;
`;

const RightNavigationButton = styled( NavigationButton )`
	background: linear-gradient(to right, rgba(29, 34, 47, 0) 0%, ${ ( { theme } ) => theme.colors.bg.v6 } 52%);
	right: ${ PAGE_WIDTH }px;
	justify-content: flex-end;
	padding-right: 10px;
`;

const Page = styled.a`
	background-color: ${ ( { isActive, theme } ) => isActive ? theme.colors.fg.v1 : theme.colors.mg.v1 };
	height: ${ PAGE_HEIGHT }px;
	width: ${ PAGE_WIDTH }px;
	margin: 0 5px;
	cursor: pointer;
	flex: none;

	&:hover {
		background-color: ${ ( { theme } ) => theme.colors.fg.v1 };
	}
`;

function CarouselPage( { page } ) {
	const { id, index } = page;
	const { state: { currentPageIndex }, actions: { setCurrentPage } } = useStory();
	const handleClickPage = () => setCurrentPage( { pageId: id } );

	return (
		<Page onClick={ handleClickPage( page ) } isActive={ index === currentPageIndex } />
	);
}

function Carousel() {
	const { state: { pages } } = useStory();
	const element = useRef();
	const [ hasHorizontalOverflow, setHasHorizontalOverflow ] = useState( false );
	const isAdminMenuCollapsed = useAdminMenuCollapsed();
	const { windowWidth } = useWindowSize();

	useEffect( () => {
		if ( element.current ) {
			const hasOverflow = element.current.scrollWidth > element.current.offsetWidth;
			setHasHorizontalOverflow( hasOverflow );
		}
	}, [ pages.length, isAdminMenuCollapsed, windowWidth ] );

	const scrollBy = ( offset ) => {
		if ( element.current ) {
			element.current.scrollBy( {
				left: offset,
				behavior: 'smooth',
			} );
		}
	};

	if ( ! hasHorizontalOverflow ) {
		return (
			<Wrapper ref={ element }>
				<List area="carousel">
					{ pages.map( ( page, index ) => (
						<CarouselPage key={ index } page={ page } />
					) ) }
				</List>
				<ActionArea area="action">
					<AddPage />
				</ActionArea>
			</Wrapper>
		);
	}

	return (
		<Wrapper hasHorizontalOverflow={ hasHorizontalOverflow }>
			<LeftNavigationButton onClick={ () => scrollBy( -( PAGE_WIDTH + 10 ) ) }>
				<LeftArrowIcon />
			</LeftNavigationButton>
			{ /* TODO: don't cut off elements at beginning and end of the list */ }
			<List area="carousel" ref={ element } hasHorizontalOverflow={ hasHorizontalOverflow }>
				{ pages.map( ( page, index ) => (
					<CarouselPage key={ index } page={ page } />
				) ) }
			</List>
			<RightNavigationButton onClick={ () => scrollBy( ( PAGE_WIDTH + 10 ) ) }>
				<RightArrowIcon />
			</RightNavigationButton>
			<ActionArea area="action">
				{ /* TODO: scroll newly added page into view */ }
				<AddPage />
			</ActionArea>
		</Wrapper>
	);
}

export default Carousel;

CarouselPage.propTypes = {
	page: PropTypes.object.isRequired,
};
