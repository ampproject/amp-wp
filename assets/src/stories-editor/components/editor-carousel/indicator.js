/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { Button, Draggable, DropZone, Tooltip } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Parses drag & drop events to ensure the event contains valid transfer data.
 *
 * @param {Object} event
 * @return {Object} Parsed event data.
 */
const parseDropEvent = ( event ) => {
	let result = {
		srcClientId: null,
		srcIndex: null,
		type: null,
	};

	if ( ! event.dataTransfer ) {
		return result;
	}

	try {
		result = Object.assign( result, JSON.parse( event.dataTransfer.getData( 'text' ) ) );
	} catch ( err ) {
		return result;
	}

	return result;
};

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

	const [ isDragging, setIsDragging ] = useState( false );
	const { movePageToPosition, initializePageOrder } = useDispatch( 'amp/story' );
	const { getBlockOrder, getBlockIndex } = useSelect( ( select ) => {
		return select( 'core/block-editor' );
	} );

	return (
		<ul className="amp-story-editor-carousel-item-list">
			{ pages.map( ( page, index ) => {
				const className = page.clientId === currentPage ? 'amp-story-editor-carousel-item amp-story-editor-carousel-item--active' : 'amp-story-editor-carousel-item';
				const blockElementId = `amp-story-editor-carousel-item-${ page.clientId }`;
				const transferData = {
					type: 'indicator',
					srcIndex: getBlockIndex( page.clientId ),
					srcClientId: page.clientId,
				};

				const getInsertIndex = ( position ) => {
					const dstIndex = getBlockIndex( page.clientId );
					if ( page.clientId !== undefined ) {
						return position.x === 'left' ? dstIndex : dstIndex + 1;
					}

					return undefined;
				};

				const onDrop = ( event, position ) => {
					const { srcClientId, srcIndex, type } = parseDropEvent( event );

					const isIndicatorDropType = ( dropType ) => dropType === 'indicator';
					const isSameBlock = ( src, dst ) => src === dst;

					if ( ! isIndicatorDropType( type ) || isSameBlock( srcClientId, page.clientId ) ) {
						return;
					}

					const positionIndex = getInsertIndex( position );
					const insertIndex = srcIndex < index ? positionIndex - 1 : positionIndex;
					movePageToPosition( srcClientId, insertIndex );
				};

				return (
					<Draggable
						key={ page.clientId }
						elementId={ blockElementId }
						transferData={ transferData }
						onDragStart={ () => {
							setIsDragging( page.clientId );
							initializePageOrder( getBlockOrder() );
						} }
						onDragEnd={ () => {
							setIsDragging( false );
						} }
					>
						{
							( { onDraggableStart, onDraggableEnd } ) => (
								<>
									<li
										className={ className }
									>
										<div
											onDragStart={ onDraggableStart }
											onDragEnd={ onDraggableEnd }
											draggable
											className="amp-story-editor-carousel-item-wrapper"
											id={ `amp-story-editor-carousel-item-${ page.clientId }` }
										>
											{ page.clientId !== currentPage && (
												<Tooltip text={ toolTip( index + 1 ) }>
													<Button
														onClick={ ( e ) => {
															e.preventDefault();
															onClick( page.clientId );
														} }
													>
														<span className="screen-reader-text">
															{ label( index + 1 ) }
														</span>
													</Button>
												</Tooltip>
											) }
											{ page.clientId === currentPage && (
												<Button
													onClick={ ( e ) => {
														e.preventDefault();
														onClick( page.clientId );
													} }
												>
													<span className="screen-reader-text">
														{ label( index + 1 ) }
													</span>
												</Button>
											) }
										</div>
										<DropZone
											className={ isDragging === page.clientId ? 'is-dragging-indicator' : undefined }
											onDrop={ onDrop }
										/>
									</li>
								</>
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
