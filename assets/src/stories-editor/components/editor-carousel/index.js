/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { DropZoneProvider, IconButton } from '@wordpress/components';
import { useEffect, useRef } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { Reorderer } from '../';
import { STORY_PAGE_INNER_WIDTH, STORY_PAGE_MARGIN } from '../../constants';
import Indicator from './indicator';
import './edit.css';

const PAGE_BORDER = 1;

const EditorCarousel = () => {
	const currentPage = useSelect( ( select ) => select( 'amp/story' ).getCurrentPage() );

	const {
		pages,
		currentIndex,
		previousPage,
		nextPage,
		isReordering,
		isRTL,
	} = useSelect( ( select ) => {
		const {
			getSettings,
			getBlockOrder,
			getBlocksByClientId,
			getAdjacentBlockClientId,
		} = select( 'core/block-editor' );
		const { isReordering: _isReordering } = select( 'amp/story' );

		const _pages = getBlocksByClientId( getBlockOrder() );

		const index = _pages.findIndex( ( { clientId } ) => clientId === currentPage );

		return {
			pages: _pages,
			currentIndex: Math.max( 0, index ), // Prevent -1 from being used for calculation.
			previousPage: currentPage ? getAdjacentBlockClientId( currentPage, -1 ) : null,
			nextPage: currentPage ? getAdjacentBlockClientId( currentPage, 1 ) : null,
			isReordering: _isReordering(),
			isRTL: getSettings().isRTL,
		};
	}, [ currentPage ] );

	const wrapper = useRef( null );

	useEffect( () => {
		wrapper.current = document.querySelector( '#amp-story-controls + .block-editor-block-list__layout' );
	}, [] );

	useEffect( () => {
		if ( isReordering ) {
			wrapper.current.style.display = 'none';
		} else {
			wrapper.current.style.display = '';

			if ( isRTL ) {
				wrapper.current.style.transform = `translateX(calc(-50% - ${ PAGE_BORDER }px + ${ ( STORY_PAGE_INNER_WIDTH + STORY_PAGE_MARGIN ) / 2 }px + ${ ( currentIndex ) * STORY_PAGE_MARGIN }px + ${ currentIndex * STORY_PAGE_INNER_WIDTH }px))`;
			} else {
				wrapper.current.style.transform = `translateX(calc(50% - ${ PAGE_BORDER }px - ${ ( STORY_PAGE_INNER_WIDTH + STORY_PAGE_MARGIN ) / 2 }px - ${ ( currentIndex ) * STORY_PAGE_MARGIN }px - ${ currentIndex * STORY_PAGE_INNER_WIDTH }px))`;
			}
		}
	}, [ currentIndex, isReordering, wrapper, isRTL ] );

	const { setCurrentPage } = useDispatch( 'amp/story' );
	const { selectBlock } = useDispatch( 'core/block-editor' );

	const goToPage = ( page ) => {
		setCurrentPage( page );
		selectBlock( page );
	};

	if ( isReordering ) {
		return <Reorderer />;
	}

	return (
		<DropZoneProvider>
			<div className="amp-story-editor-carousel-navigation">
				<IconButton
					icon={ isRTL ? 'arrow-right-alt2' : 'arrow-left-alt2' }
					label={ __( 'Previous Page', 'amp' ) }
					onClick={ ( e ) => {
						e.preventDefault();
						goToPage( previousPage );
					} }
					disabled={ null === previousPage }
				/>
				<Indicator
					pages={ pages }
					currentPage={ currentPage }
					onClick={ goToPage }
				/>
				<IconButton
					icon={ isRTL ? 'arrow-left-alt2' : 'arrow-right-alt2' }
					label={ __( 'Next Page', 'amp' ) }
					onClick={ ( e ) => {
						e.preventDefault();
						goToPage( nextPage );
					} }
					disabled={ null === nextPage }
				/>
			</div>
		</DropZoneProvider>
	);
};

export default EditorCarousel;
