/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { Button, Draggable, DropZone, Tooltip } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { parseDropEvent } from '../../helpers';

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

	const [ draggedPage, setDraggedPage ] = useState( null );
	const { moveBlockToPosition } = useDispatch( 'core/block-editor' );
	const { getBlockIndex } = useSelect( ( select ) => select( 'core/block-editor' ) );

	return (
		<ul className="amp-story-editor-carousel-item-list">
			{ pages.map( ( page, index ) => {
				const { clientId } = page;
				const className = classnames( 'amp-story-editor-carousel-item', {
					'amp-story-editor-carousel-item--active': clientId === currentPage,
				} );
				const blockElementId = `amp-story-editor-carousel-item-${ clientId }`;
				const transferData = {
					type: 'indicator',
					srcIndex: getBlockIndex( clientId ),
					srcClientId: clientId,
				};

				const getInsertIndex = ( position ) => {
					const dstIndex = getBlockIndex( clientId );
					if ( clientId !== undefined ) {
						// Considering the horizontal layout of the indicators.
						// If the dropzone is on the left from the destination page
						// Then the destination index is correct, otherwise it should be one higher.
						return position.x === 'left' ? dstIndex : dstIndex + 1;
					}

					return undefined;
				};

				const onDrop = ( event, position ) => {
					const { srcClientId, srcIndex, type } = parseDropEvent( event );

					const isIndicatorDropType = 'indicator' === type;
					const isSameBlock = srcClientId === clientId;

					if ( ! isIndicatorDropType || isSameBlock ) {
						return;
					}

					let insertIndex = getInsertIndex( position );
					// If we move the page forward, we should also account for the "vacant" place of the page itself.
					if ( insertIndex > srcIndex ) {
						insertIndex--;
					}
					moveBlockToPosition( srcClientId, '', '', insertIndex );
				};

				const isPageDragged = clientId === draggedPage;
				const isCurrentPage = clientId === currentPage;
				const indicatorButton = (
					<Button
						onClick={ ( e ) => {
							e.preventDefault();
							onClick( clientId );
						} }
					>
						<span className="indicator-page-number">
							{ index + 1 }
						</span>
						<span className="screen-reader-text">
							{ label( index + 1 ) }
						</span>
					</Button>
				);

				return (
					<Draggable
						key={ clientId }
						elementId={ blockElementId }
						transferData={ transferData }
						onDragStart={ () => {
							setDraggedPage( clientId );
						} }
						onDragEnd={ () => {
							setDraggedPage( null );
						} }
					>
						{
							( { onDraggableStart, onDraggableEnd } ) => (
								<li
									className={ className }
								>
									<div
										onDragStart={ onDraggableStart }
										onDragEnd={ onDraggableEnd }
										draggable
										className="amp-story-editor-carousel-item-wrapper"
										id={ blockElementId }
									>
										{ isCurrentPage ?
											indicatorButton :
											<Tooltip text={ toolTip( index + 1 ) }>
												{ indicatorButton }
											</Tooltip>
										}
									</div>
									<DropZone
										className={ classnames( '', { 'is-dragging-indicator': isPageDragged } ) }
										onDrop={ onDrop }
									/>
								</li>
							)
						}
					</Draggable>
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
