/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { IconButton } from '@wordpress/components';
import { Fragment, Component } from '@wordpress/element';
import { withDispatch, withSelect } from '@wordpress/data';
import { compose } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import Indicator from './indicator';

const PAGE_WIDTH = 350;
const WRAPPER_PADDING = 15;

class EditorCarousel extends Component {
	constructor( props ) {
		super( props );

		this.state = {
			index: 0,
		};

		this.translateWrapper.bind( this );
	}

	translateWrapper() {
		const wrapper = document.querySelector( '.editor-writing-flow .editor-block-list__layout' );
		wrapper.style.transform = `translateX(calc(50% - ${ PAGE_WIDTH / 2 }px - ${ WRAPPER_PADDING }px - ${ ( this.state.index ) * PAGE_WIDTH }px))`;
	}

	componentDidMount() {
		this.translateWrapper();
	}

	componentDidUpdate() {
		this.translateWrapper();
	}

	render() {
		const { pages, currentPage, previousPage, nextPage, onChangePage } = this.props;

		const goToPage = ( page ) => {
			onChangePage( page );

			const index = pages.findIndex( ( { clientId } ) => clientId === page );

			if ( -1 !== index ) {
				this.setState( { index } );
			}
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
