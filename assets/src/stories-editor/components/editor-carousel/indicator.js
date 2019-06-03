/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { Button, Tooltip } from '@wordpress/components';

/**
 * Carousel indicator component.
 *
 * "Progress bar"-style indicator at the bottom of the pages carousel,
 * indicating the number of pages and the currently selected one.
 *
 * @param {Object}   props             Indicator props.
 * @param {Array}    props.pages       Pages to list.
 * @param {string}   props.currentPage The currently selected page.
 * @param {Function} props.onClick     onClick callback.
 *
 * @return {Object} Carousel indicator.
 */
const Indicator = ( { pages, currentPage, onClick } ) => {
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
};

Indicator.propTypes = {
	pages: PropTypes.arrayOf( PropTypes.shape( {
		clientId: PropTypes.string,
	} ) ),
	currentPage: PropTypes.string,
	onClick: PropTypes.func,
};

export default Indicator;
