/**
 * External dependencies
 */
import styled from 'styled-components';
import ResizeObserver from 'resize-observer-polyfill';

/**
 * WordPress dependencies
 */
import { useLayoutEffect, useRef, useState, useCallback } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { useStory } from '../../../app';
import { LeftArrow, RightArrow, GridView } from '../../button';

const PAGE_WIDTH = 72;
const PAGE_HEIGHT = 128;

const Wrapper = styled.div`
	position: relative;
	display: grid;
	grid: "left-navigation carousel right-navigation" auto / 53px 1fr 53px;
	color:  ${ ( { theme } ) => theme.colors.fg.v1 };
`;

const Area = styled.div`
	grid-area: ${ ( { area } ) => area };
	display: flex;
	align-items: center;
	justify-content: center;
	flex-direction: column;
	padding: 16px 0 24px;
`;

const List = styled( Area )`
	flex-direction: row;
	align-items: flex-start;
	justify-content: ${ ( { hasHorizontalOverflow } ) => hasHorizontalOverflow ? 'flex-start' : 'center' };
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

const GridViewButton = styled( GridView )`
	position: absolute;
	bottom: 24px;
`;

function Carousel() {
	const { state: { pages, currentPageIndex, currentPageId }, actions: { setCurrentPage } } = useStory();
	const [ hasHorizontalOverflow, setHasHorizontalOverflow ] = useState( false );
	const listRef = useRef();
	const pageRefs = useRef( [] );

	useLayoutEffect( () => {
		const observer = new ResizeObserver( ( entries ) => {
			for ( const entry of entries ) {
				const offsetWidth = entry.contentBoxSize ? entry.contentBoxSize.inlineSize : entry.contentRect.width;
				setHasHorizontalOverflow( Math.ceil( listRef.current.scrollWidth ) > Math.ceil( offsetWidth ) );
			}
		} );

		observer.observe( listRef.current );

		return () => observer.disconnect();
	}, [ pages.length ] );

	useLayoutEffect( () => {
		if ( hasHorizontalOverflow ) {
			const currentPageRef = pageRefs.current[ currentPageId ];

			if ( ! currentPageRef || ! currentPageRef.scrollIntoView ) {
				return;
			}

			currentPageRef.scrollIntoView( {
				inline: 'center',
				behavior: 'smooth',
			} );
		}
	}, [ currentPageId, hasHorizontalOverflow, pageRefs ] );

	const handleClickPage = ( page ) => () => setCurrentPage( { pageId: page.id } );

	const scrollBy = useCallback( ( offset ) => {
		if ( ! listRef.current.scrollBy ) {
			listRef.current.scrollLeft += offset;
			return;
		}

		listRef.current.scrollBy( {
			left: offset,
			behavior: 'smooth',
		} );
	}, [ listRef ] );

	return (
		<Wrapper>
			<Area area="left-navigation">
				<LeftArrow
					isHidden={ ! hasHorizontalOverflow }
					onClick={ () => scrollBy( -( 2 * PAGE_WIDTH ) ) }
					width="24"
					height="24"
					aria-label={ __( 'Scroll Left', 'amp' ) }
				/>
			</Area>
			<List area="carousel" ref={ listRef } hasHorizontalOverflow={ hasHorizontalOverflow }>
				{ pages.map( ( page, index ) => {
					const isCurrentPage = index === currentPageIndex;
					return (
						<Page
							key={ index }
							onClick={ handleClickPage( page ) }
							isActive={ isCurrentPage }
							ref={ ( el ) => {
								pageRefs.current[ page.id ] = el;
							} }
							aria-label={ isCurrentPage ? sprintf( __( 'Page %s (current page)', 'amp' ), index + 1 ) : sprintf( __( 'Go to page %s', 'amp' ), index + 1 ) }
						/>
					);
				} ) }
			</List>
			<Area area="right-navigation">
				<RightArrow
					isHidden={ ! hasHorizontalOverflow }
					onClick={ () => scrollBy( ( 2 * PAGE_WIDTH ) ) }
					width="24"
					height="24"
					aria-label={ __( 'Scroll Right', 'amp' ) }
				/>
				<GridViewButton
					isDisabled
					width="24"
					height="24"
					aria-label={ __( 'Grid View', 'amp' ) }
				/>
			</Area>
		</Wrapper>
	);
}

export default Carousel;
