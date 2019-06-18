/**
 * External dependencies
 */
import classnames from 'classnames';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { ResizableBox } from '@wordpress/components';

/**
 * Internal dependencies
 */
import './edit.css';
import {
	getResizedWidthAndHeight,
	getPercentageFromPixels,
	getPixelsFromPercentage,
	getBlockPositioning,
	getRadianFromDeg,
} from '../../helpers';

import { BLOCKS_WITH_TEXT_SETTINGS, TEXT_BLOCK_BORDER, TEXT_BLOCK_PADDING } from '../../constants';

let lastSeenX = 0,
	lastSeenY = 0,
	lastWidth,
	lastHeight,
	blockElement = null,
	blockElementTop,
	blockElementLeft,
	imageWrapper,
	textBlockWrapper,
	textElement;

const EnhancedResizableBox = ( props ) => {
	const {
		isSelected,
		angle,
		blockName,
		ampFitText,
		minWidth,
		minHeight,
		onResizeStart,
		onResizeStop,
		children,
		...otherProps
	} = props;

	let {
		width,
		height,
	} = props;

	const isImage = 'core/image' === blockName;
	const isBlockWithText = BLOCKS_WITH_TEXT_SETTINGS.includes( blockName ) || 'core/code' === blockName;
	const isText = 'amp/amp-story-text' === blockName;

	if ( isText ) {
		height += TEXT_BLOCK_PADDING * 2;
		width += TEXT_BLOCK_PADDING * 2;
	}

	const textBlockBorderInPercentageTop = getPercentageFromPixels( 'y', TEXT_BLOCK_BORDER );
	const textBlockBorderInPercentageLeft = getPercentageFromPixels( 'x', TEXT_BLOCK_BORDER );

	return (
		<ResizableBox
			{ ...otherProps }
			className={ classnames(
				'amp-story-resize-container',
				{ 'is-selected': isSelected }
			) }
			size={ {
				height,
				width,
			} }
			// Adding only right and bottom since otherwise it needs to change the top and left position, too.
			enable={ {
				top: false,
				right: true,
				bottom: true,
				left: false,
			} }
			onResizeStop={ ( event, direction ) => {
				const { deltaW, deltaH } = getResizedWidthAndHeight( event, angle, lastSeenX, lastSeenY, direction );
				let appliedWidth = width + deltaW;
				let appliedHeight = height + deltaH;

				// Ensure the measures not crossing limits.
				appliedWidth = appliedWidth < lastWidth ? lastWidth : appliedWidth;
				appliedHeight = appliedHeight < lastHeight ? lastHeight : appliedHeight;

				const elementTop = parseFloat( blockElement.style.top );
				const elementLeft = parseFloat( blockElement.style.left );

				const positionTop = ! isText ? Number( elementTop.toFixed( 2 ) ) : Number( ( elementTop + textBlockBorderInPercentageTop ).toFixed( 2 ) );
				const positionLeft = ! isText ? Number( elementLeft.toFixed( 2 ) ) : Number( ( elementLeft + textBlockBorderInPercentageLeft ).toFixed( 2 ) );

				onResizeStop( {
					width: isText ? parseInt( appliedWidth, 10 ) - ( TEXT_BLOCK_PADDING * 2 ) : parseInt( appliedWidth, 10 ),
					height: isText ? parseInt( appliedHeight, 10 ) - ( TEXT_BLOCK_PADDING * 2 ) : parseInt( appliedHeight, 10 ),
					positionTop,
					positionLeft,
				} );
			} }
			onResizeStart={ ( event, direction, element ) => {
				lastSeenX = event.clientX;
				lastSeenY = event.clientY;
				lastWidth = width;
				lastHeight = height;
				blockElement = element.closest( '.wp-block' );
				blockElementTop = blockElement.style.top;
				blockElementLeft = blockElement.style.left;
				if ( isImage ) {
					imageWrapper = blockElement.querySelector( 'figure .components-resizable-box__container' );
				}
				if ( isBlockWithText && ! ampFitText ) {
					switch ( blockName ) {
						case 'amp/amp-story-text':
							textElement = blockElement.querySelector( '.block-editor-rich-text__editable.editor-rich-text__editable' );
							break;
						case 'amp/amp-story-post-title':
							textElement = blockElement.querySelector( '.wp-block-amp-amp-story-post-title' );
							break;
						case 'amp/amp-story-post-author':
							textElement = blockElement.querySelector( '.wp-block-amp-amp-story-post-author' );
							break;
						case 'amp/amp-story-post-date':
							textElement = blockElement.querySelector( '.wp-block-amp-amp-story-post-date' );
							break;
						case 'core/code':
							textElement = blockElement.querySelector( '.wp-block-code' );
					}
				} else {
					textElement = null;
				}

				if ( ampFitText && isText ) {
					textBlockWrapper = blockElement.querySelector( '.with-line-height' );
				} else {
					textBlockWrapper = null;
				}

				onResizeStart();
			} }
			onResize={ ( event, direction, element ) => {
				const { deltaW, deltaH } = getResizedWidthAndHeight( event, angle, lastSeenX, lastSeenY, direction );

				// Handle case where media is inserted from URL.
				if ( isImage && ! width && ! height ) {
					width = blockElement.clientWidth;
					height = blockElement.clientHeight;
				}
				let appliedWidth = minWidth <= width + deltaW ? width + deltaW : minWidth;
				let appliedHeight = minHeight <= height + deltaH ? height + deltaH : minHeight;
				const isReducing = 0 > deltaW || 0 > deltaH;

				if ( textElement && isReducing ) {
					// If we have a rotated block, let's assign the width and height for measuring.
					// Without assigning the new measure, the calculation would be incorrect due to angle.
					// Text block is handled differently since the text block's content shouldn't have full width while measuring.
					if ( angle ) {
						if ( ! isText ) {
							textElement.style.width = appliedWidth + 'px';
							textElement.style.height = appliedHeight + 'px';
						} else if ( isText && ! ampFitText ) {
							textElement.style.width = 'initial';
						}
					}

					const scrollWidth = isText ? textElement.scrollWidth + ( TEXT_BLOCK_BORDER * 2 ) : textElement.scrollWidth;
					const scrollHeight = isText ? textElement.scrollHeight + ( TEXT_BLOCK_BORDER * 2 ) : textElement.scrollHeight;
					if ( appliedWidth < scrollWidth || appliedHeight < scrollHeight ) {
						appliedWidth = lastWidth;
						appliedHeight = lastHeight;
					}
					// If we have rotated block, let's restore the correct measures.
					if ( angle ) {
						if ( ! isText ) {
							textElement.style.width = 'initial';
							textElement.style.height = '100%';
						} else if ( isText && ! ampFitText ) {
							textElement.style.width = '100%';
						}
					}
				}

				if ( angle ) {
					const radianAngle = getRadianFromDeg( angle );

					// Compare position between the initial and after resizing.
					let initialPosition, resizedPosition;
					// If it's a text block, we shouldn't consider the added padding for measuring.
					if ( isText ) {
						initialPosition = getBlockPositioning( width - ( TEXT_BLOCK_PADDING * 2 ), height - ( TEXT_BLOCK_PADDING * 2 ), radianAngle );
						resizedPosition = getBlockPositioning( appliedWidth - ( TEXT_BLOCK_PADDING * 2 ), appliedHeight - ( TEXT_BLOCK_PADDING * 2 ), radianAngle );
					} else {
						initialPosition = getBlockPositioning( width, height, radianAngle );
						resizedPosition = getBlockPositioning( appliedWidth, appliedHeight, radianAngle );
					}
					const diff = {
						left: resizedPosition.left - initialPosition.left,
						top: resizedPosition.top - initialPosition.top,
					};
					// Get new position based on the difference.
					const originalPos = {
						left: getPixelsFromPercentage( 'x', parseFloat( blockElementLeft ) ),
						top: getPixelsFromPercentage( 'y', parseFloat( blockElementTop ) ),
					};

					// @todo Figure out why calculating the new top / left position doesn't work in case of small height value.
					// @todo Remove this temporary fix.
					if ( appliedHeight < 60 ) {
						diff.left = diff.left / ( 60 / appliedHeight );
						diff.right = diff.right / ( 60 / appliedHeight );
					}

					const updatedPos = {
						left: originalPos.left - diff.left,
						top: originalPos.top + diff.top,
					};

					blockElement.style.left = getPercentageFromPixels( 'x', updatedPos.left ) + '%';
					blockElement.style.top = getPercentageFromPixels( 'y', updatedPos.top ) + '%';
				}

				element.style.width = appliedWidth + 'px';
				element.style.height = appliedHeight + 'px';

				lastWidth = appliedWidth;
				lastHeight = appliedHeight;

				if ( textBlockWrapper && ampFitText ) {
					textBlockWrapper.style.lineHeight = isText ? appliedHeight - ( TEXT_BLOCK_PADDING * 2 ) + 'px' : appliedHeight + 'px';
				}

				// If it's image, let's change the width and height of the image, too.
				if ( imageWrapper && isImage ) {
					imageWrapper.style.width = appliedWidth + 'px';
					imageWrapper.style.height = appliedHeight + 'px';
				}
			} }
		>
			{ children }
		</ResizableBox>
	);
};

EnhancedResizableBox.propTypes = {
	isSelected: PropTypes.bool,
	ampFitText: PropTypes.bool,
	angle: PropTypes.number,
	blockName: PropTypes.string,
	minWidth: PropTypes.number,
	minHeight: PropTypes.number,
	onResizeStart: PropTypes.func.isRequired,
	onResizeStop: PropTypes.func.isRequired,
	children: PropTypes.any.isRequired,
	width: PropTypes.number,
	height: PropTypes.number,
};

export default EnhancedResizableBox;
