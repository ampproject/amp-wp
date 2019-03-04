/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { IconButton, Button, Tooltip } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { withDispatch, withSelect } from '@wordpress/data';
import { compose } from '@wordpress/compose';

function Indicator( { pages, currentPage, onClick } ) {
	/* translators: %s: Page number */
	const label = ( pageNumber ) => sprintf( __( 'Page %s', 'amp' ), pageNumber );

	/* translators: %s: Page number */
	const toolTip = ( pageNumber ) => sprintf( __( 'Go to page %s', 'amp' ), pageNumber );

	return (
		<ul className="amp-story-editor-carousel-item-list">
			{ pages.map( ( page, index ) => {
				const className = page.clientId === currentPage ? 'amp-story-editor-carousel-item amp-story-editor-carousel-item--active' : 'amp-story-editor-carousel-item';

				return (
					<li key={ page.clientId } className={ className }>
						<Tooltip text={ toolTip( index + 1 ) }>
							<Button
								onClick={ ( e ) => {
									e.preventDefault();
									onClick( page.clientId );
								} }
								disabled={ page.clientId === currentPage }
							>
								<span className="screen-reader-text">
									{ label( index + 1 ) }
								</span>
							</Button>
						</Tooltip>
					</li>
				);
			} ) }
		</ul>
	);
}

function EditorCarousel( { pages, currentPage, onChangePage, previousPage, nextPage } ) {
	const goToPage = ( page ) => {
		onChangePage( page );

		const wrapper = document.querySelector( '.editor-writing-flow .editor-block-list__layout' );
		const blockElement = document.querySelector( `[data-block="${ page }"]` );

		// Todo: Fix calculation.
		wrapper.scrollLeft = blockElement.getBoundingClientRect().x - wrapper.getBoundingClientRect().x;
	};

	return (
		<Fragment>
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
		</Fragment>
	);
}

export default compose(
	withSelect( ( select ) => {
		const {
			getBlockOrder,
			getBlocksByClientId,
			getAdjacentBlockClientId,
		} = select( 'core/editor' );
		const { getCurrentPage } = select( 'amp/story' );

		return {
			pages: getBlocksByClientId( getBlockOrder() ),
			// Todo: Use state for the following properties/methods.
			currentPage: getCurrentPage(),
			previousPage: getCurrentPage() ? getAdjacentBlockClientId( getCurrentPage(), -1 ) : null,
			nextPage: getCurrentPage() ? getAdjacentBlockClientId( getCurrentPage(), 1 ) : null,
		};
	} ),
	withDispatch( ( dispatch ) => {
		const { setCurrentPage } = dispatch( 'amp/story' );

		return {
			onChangePage: ( pageClientId ) => {
				setCurrentPage( pageClientId );
			},
		};
	} )
)( EditorCarousel );
