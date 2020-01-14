/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useEffect, useRef, useState, createRef, useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useStory } from '../../../app';
import LeftArrowIcon from './leftArrow.svg';
import RightArrowIcon from './rightArrow.svg';
import GridViewIcon from './gridView.svg';

const PAGE_WIDTH = 72;
const PAGE_HEIGHT = 128;

const Wrapper = styled.div`
	position: relative;
	display: grid;
	grid: "left-navigation carousel right-navigation" auto / 53px 1fr 53px;
	height: 100%;
	width: 100%;
	color:  ${ ( { theme } ) => theme.colors.fg.v1 };
`;

const Area = styled.div`
	grid-area: ${ ( { area } ) => area };
	height: 100%;
	width: 100%;
	display: flex;
	align-items: center;
	justify-content: center;
	flex-direction: column;
`;

const List = styled( Area )`
	flex-direction: row;
	align-items: flex-start;
	justify-content: ${ ( { hasHorizontalOverflow } ) => hasHorizontalOverflow ? 'space-between' : 'center' };
	height: 100%;
	padding: 16px 0 24px;
	overflow-x: ${ ( { hasHorizontalOverflow } ) => hasHorizontalOverflow ? 'scroll' : 'hidden' };
`;

const Page = styled.button`
	padding: 0;
	margin: 0 5px;
	border: 3px solid ${ ( { isActive, theme } ) => isActive ? theme.colors.selection : theme.colors.bg.v1 };
	height: ${ PAGE_HEIGHT }px;
	width: ${ PAGE_WIDTH }px;
	background-color: ${ ( { isActive, theme } ) => isActive ? theme.colors.fg.v1 : theme.colors.mg.v1 };
	flex: none;
`;

const IconButton = styled.button`
	width: 53px;
	height: 28px;
	padding: 0;
	margin: 0;
	border: none;
	color: ${ ( { theme } ) => theme.colors.fg.v1 };
	background: transparent;
	svg {
		width: 2em;
		height: 2em;
	}
`;

const GridViewButton = styled( IconButton )`
	position: absolute;
	bottom: 24px;
`;

function Carousel() {
	const { state: { pages, currentPageIndex, currentPageId }, actions: { setCurrentPage } } = useStory();
	const [ hasHorizontalOverflow, setHasHorizontalOverflow ] = useState( false );
	const listRef = useRef();

	const pageRefs = pages.reduce( ( acc, page ) => {
		acc[ page.id ] = createRef();
		return acc;
	}, {} );

	useEffect( () => {
		const { ResizeObserver } = window;

		const observer = new ResizeObserver( ( entries ) => {
			for ( const entry of entries ) {
				const offsetWidth = entry.contentBoxSize ? entry.contentBoxSize.inlineSize : entry.contentRect.width;
				setHasHorizontalOverflow( listRef.current.scrollWidth > offsetWidth );
			}
		} );

		if ( listRef.current ) {
			observer.observe( listRef.current );
		}

		return () => {
			observer.disconnect();
		};
	} );

	useEffect( () => {
		if ( hasHorizontalOverflow ) {
			const currentPageRef = pageRefs[ currentPageId ];

			if ( currentPageRef.current ) {
				// TODO: Don't actually scroll vertically.
				currentPageRef.current.scrollIntoView( {
					inline: 'center',
					behavior: 'smooth',
				} );
			}
		}
	}, [ currentPageId, hasHorizontalOverflow, pageRefs ] );

	const handleClickPage = ( page ) => () => setCurrentPage( { pageId: page.id } );

	const scrollBy = useCallback( ( offset ) => {
		if ( listRef.current ) {
			listRef.current.scrollBy( {
				left: offset,
				behavior: 'smooth',
			} );
		}
	}, [ listRef ] );

	return (
		<Wrapper>
			<Area area="left-navigation">
				{ hasHorizontalOverflow && (
					<IconButton onClick={ () => scrollBy( -( 2 * PAGE_WIDTH ) ) }>
						<LeftArrowIcon />
					</IconButton>
				) }
			</Area>
			<List area="carousel" ref={ listRef } hasHorizontalOverflow={ hasHorizontalOverflow }>
				{ pages.map( ( page, index ) => (
					<Page
						key={ index }
						onClick={ handleClickPage( page ) }
						isActive={ index === currentPageIndex }
						ref={ pageRefs[ page.id ] }
					/>
				) ) }
			</List>
			<Area area="right-navigation">
				{ hasHorizontalOverflow && (
					<IconButton onClick={ () => scrollBy( ( 2 * PAGE_WIDTH ) ) }>
						<RightArrowIcon />
					</IconButton>
				) }
				<GridViewButton disabled>
					<GridViewIcon />
				</GridViewButton>
			</Area>
		</Wrapper>
	);
}

export default Carousel;
