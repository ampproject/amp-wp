/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { IconButton } from '@wordpress/components';
import { Component } from '@wordpress/element';
import { withDispatch, withSelect } from '@wordpress/data';
import { compose } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import Indicator from './indicator';
import { Reorderer } from '../';
import { STORY_PAGE_INNER_WIDTH } from '../../constants';
import './edit.css';

// This is the sum of left (20px) and right (30px) margin.
const TOTAL_PAGE_MARGIN = 50;
const PAGE_BORDER = 1;

class EditorCarousel extends Component {
	constructor() {
		super( ...arguments );

		this.translateWrapper.bind( this );
	}

	translateWrapper() {
		const wrapper = document.querySelector( '#amp-story-controls + .block-editor-block-list__layout' );

		if ( ! wrapper ) {
			return;
		}

		const { currentIndex } = this.props;

		if ( this.props.isReordering ) {
			wrapper.style.display = 'none';
		} else {
			wrapper.style.display = '';
			wrapper.style.transform = `translateX(calc(50% - ${ PAGE_BORDER }px - ${ ( STORY_PAGE_INNER_WIDTH + TOTAL_PAGE_MARGIN ) / 2 }px - ${ ( currentIndex ) * TOTAL_PAGE_MARGIN }px - ${ currentIndex * STORY_PAGE_INNER_WIDTH }px))`;
		}
	}

	componentDidMount() {
		this.translateWrapper();
	}

	componentDidUpdate() {
		this.translateWrapper();
	}

	render() {
		const { pages, currentPage, previousPage, nextPage, onChangePage, isReordering } = this.props;

		const goToPage = ( page ) => {
			onChangePage( page );
		};

		if ( isReordering ) {
			return <Reorderer />;
		}

		return (
			<>
				<div className="amp-story-editor-carousel-navigation">
					<IconButton
						icon="arrow-left-alt2"
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
						icon="arrow-right-alt2"
						label={ __( 'Next Page', 'amp' ) }
						onClick={ ( e ) => {
							e.preventDefault();
							goToPage( nextPage );
						} }
						disabled={ null === nextPage }
					/>
				</div>
			</>
		);
	}
}

EditorCarousel.propTypes = {
	pages: PropTypes.arrayOf( PropTypes.shape( {
		clientId: PropTypes.string,
	} ) ),
	currentIndex: PropTypes.number.isRequired,
	currentPage: PropTypes.string,
	previousPage: PropTypes.string,
	nextPage: PropTypes.string,
	onChangePage: PropTypes.func.isRequired,
	isReordering: PropTypes.bool,
};

export default compose(
	withSelect( ( select ) => {
		const {
			getBlockOrder,
			getBlocksByClientId,
			getAdjacentBlockClientId,
		} = select( 'core/block-editor' );
		const { getCurrentPage, isReordering } = select( 'amp/story' );

		const currentPage = getCurrentPage();
		const pages = getBlocksByClientId( getBlockOrder() );

		const currentIndex = pages.findIndex( ( { clientId } ) => clientId === currentPage );

		return {
			pages,
			currentPage,
			currentIndex: Math.max( 0, currentIndex ), // Prevent -1 from being used for calculation.
			previousPage: getCurrentPage() ? getAdjacentBlockClientId( currentPage, -1 ) : null,
			nextPage: getCurrentPage() ? getAdjacentBlockClientId( currentPage, 1 ) : null,
			isReordering: isReordering(),
		};
	} ),
	withDispatch( ( dispatch ) => {
		const { setCurrentPage } = dispatch( 'amp/story' );
		const { selectBlock } = dispatch( 'core/block-editor' );

		return {
			onChangePage: ( pageClientId ) => {
				setCurrentPage( pageClientId );
				selectBlock( pageClientId );
			},
		};
	} )
)( EditorCarousel );
